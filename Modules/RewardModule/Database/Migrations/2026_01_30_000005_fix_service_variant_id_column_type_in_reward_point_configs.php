<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixServiceVariantIdColumnTypeInRewardPointConfigs extends Migration
{
    /**
     * variations.id is unsigned big integer (auto-increment), not UUID.
     * Fix service_variant_id column to match and correct wrongly padded values.
     *
     * @return void
     */
    public function up()
    {
        $db = DB::getDatabaseName();
        $fkName = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'reward_point_configs'
            AND COLUMN_NAME = 'service_variant_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db]);
        if ($fkName && !empty($fkName->CONSTRAINT_NAME)) {
            DB::statement('ALTER TABLE reward_point_configs DROP FOREIGN KEY ' . $fkName->CONSTRAINT_NAME);
        }

        $indexes = DB::select("SHOW INDEX FROM reward_point_configs WHERE Column_name = 'service_variant_id'");
        $dropped = [];
        foreach ($indexes as $idx) {
            $name = $idx->Key_name;
            if ($name === 'PRIMARY' || isset($dropped[$name])) {
                continue;
            }
            $dropped[$name] = true;
            DB::statement("ALTER TABLE reward_point_configs DROP INDEX `{$name}`");
        }

        // Fix existing data: trim trailing zeros (e.g. "36303934000000000000000000000000" -> 36303934)
        DB::statement("
            UPDATE reward_point_configs
            SET service_variant_id = CAST(TRIM(TRAILING '0' FROM service_variant_id) AS UNSIGNED)
            WHERE service_variant_id IS NOT NULL
            AND service_variant_id REGEXP '^[0-9]+0*$'
            AND LENGTH(TRIM(TRAILING '0' FROM service_variant_id)) <= 20
        ");

        // For any remaining non-numeric or too-long values, set to NULL so the column can be altered
        DB::statement("
            UPDATE reward_point_configs
            SET service_variant_id = NULL
            WHERE service_variant_id IS NOT NULL
            AND (service_variant_id NOT REGEXP '^[0-9]+$' OR LENGTH(service_variant_id) > 20)
        ");

        DB::statement('ALTER TABLE reward_point_configs MODIFY service_variant_id BIGINT UNSIGNED NULL');

        Schema::table('reward_point_configs', function (Blueprint $table) {
            $table->foreign('service_variant_id')
                ->references('id')
                ->on('variations')
                ->cascadeOnDelete();
            $table->index('service_variant_id');
            $table->index(['service_variant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reward_point_configs', function (Blueprint $table) {
            $table->dropForeign(['service_variant_id']);
            $table->dropIndex(['service_variant_id', 'is_active']);
            $table->dropIndex(['service_variant_id']);
        });
        DB::statement('ALTER TABLE reward_point_configs MODIFY service_variant_id CHAR(36) NULL');
        Schema::table('reward_point_configs', function (Blueprint $table) {
            $table->foreign('service_variant_id')
                ->references('id')
                ->on('variations')
                ->cascadeOnDelete();
            $table->index('service_variant_id');
            $table->index(['service_variant_id', 'is_active']);
        });
    }
}
