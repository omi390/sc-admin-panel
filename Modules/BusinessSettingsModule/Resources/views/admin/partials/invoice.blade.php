
<div class="invoice-box-wrap">
    <div class="invoice-box">
        <div class="d-flex justify-content-between mb-3">
            <div class="d-flex flex-column gap-1">
                <h2>INVOICE</h2>
                <div>Transaction ID: #0100082</div>
                <div>Date: 23 July, 2023</div>
            </div>
            <div class="d-flex flex-column gap-1 align-items-end text-right">
                <img
                    width="84"
                    class="mb-1"
                    src="{{asset('public/assets/admin-module')}}/img/logo.png"
                    alt=""
                />
                <div>
                    L: 02, H: 1005, 1007, <br />
                    Av: 11, R: 09, Dhaka 1216, BD
                </div>
                <div>+8801100000001</div>
                <div>demandium@demo.com</div>
            </div>
        </div>

        <div class="invoice-card">
            <div class="invoice-card__head">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex gap-4">
                        <div>
                            <div>Provider</div>
                            <div class="fs-10">Jhone Doe</div>
                        </div>
                        <div>
                            <div>Phone</div>
                            <div class="fs-10">+9154983134435</div>
                        </div>
                        <div>
                            <div>Email</div>
                            <div class="fs-10">jhone@example.com</div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 align-items-end text-right">
                        <div>Invoice of (USD)</div>
                        <h3 class="text-primary">$500</h3>
                    </div>
                </div>
            </div>
            <div class="invoice-card__body">
                <div class="meta-info d-flex mb-30">
                    <div>
                        <div>Payment</div>
                        <div class="fs-10">Stripe</div>
                    </div>
                    <div class="border-left"></div>
                    <div>
                        <div>Purchased</div>
                        <div class="fs-10">Standard Package</div>
                    </div>
                    <div class="border-left"></div>
                    <div>
                        <div>Duration</div>
                        <div class="fs-10">365 Days</div>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Package Name</th>
                            <th>Time</th>
                            <th>Validity</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>2398435734</td>
                            <td>Standard</td>
                            <td>23 Jul, 2023</td>
                            <td>365 Days</td>
                            <td>$500</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-10 text-center">Thanks for using our service.</div>
            </div>
        </div>
    </div>
    <div class="invoice-footer">All rights reserved By @DemandiumLtd 2024</div>
</div>
