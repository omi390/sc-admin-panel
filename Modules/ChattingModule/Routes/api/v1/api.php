<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::get('channel-list', 'ChattingController@channelList');
        Route::get('referenced-channel-list', 'ChattingController@referencedChannelList');
        Route::post('create-channel', 'ChattingController@createChannel');
        Route::post('send-message', 'ChattingController@sendMessage');
        Route::get('conversation', 'ChattingController@conversation');
    });
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::get('channel-list', 'ChattingController@channelList');
        Route::post('channel-list-search', 'ChattingController@channelListSearch');
        Route::get('referenced-channel-list', 'ChattingController@referencedChannelList');
        Route::post('create-channel', 'ChattingController@createChannel');
        Route::post('send-message', 'ChattingController@sendMessage');
        Route::get('conversation', 'ChattingController@conversation');
    });
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::get('channel-list', 'ChattingController@channelList');
        Route::post('channel-list-search', 'ChattingController@channelListSearch');
        Route::get('referenced-channel-list', 'ChattingController@referencedChannelList');
        Route::post('create-channel', 'ChattingController@createChannel');
        Route::post('send-message', 'ChattingController@sendMessage');
        Route::get('conversation', 'ChattingController@conversation');
    });
});

Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'namespace' => 'Api\V1\Serviceman', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::get('channel-list', 'ChattingController@channelList');
        Route::post('channel-list-search', 'ChattingController@channelListSearch');
        Route::get('referenced-channel-list', 'ChattingController@referencedChannelList');
        Route::post('create-channel', 'ChattingController@createChannel');
        Route::post('send-message', 'ChattingController@sendMessage');
        Route::get('conversation', 'ChattingController@conversation');
    });
});

Route::group(['namespace' => 'Api\V1', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'chat'], function () {
        Route::get('channel-list', 'GlobalChattingController@channelList');
        Route::get('referenced-channel-list', 'GlobalChattingController@referencedChannelList');
        Route::post('create-channel', 'GlobalChattingController@createChannel');
        Route::post('send-message', 'GlobalChattingController@sendMessage');
        Route::get('conversation', 'GlobalChattingController@conversation');
        Route::get('unread-conversation', 'GlobalChattingController@unreadConversationCount');
    });
});
