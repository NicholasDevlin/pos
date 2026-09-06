<div id="print">
    <style>
        * {
            font-size: 14px;
        }

        body {
            margin: 0;
        }

        .page-break {
            page-break-before: always;
        }

        .border {
            border: 1px dotted rgb(0 0 0 / 0.45);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        table td {
            padding: 4px;
        }

        @media print {
            @page a4page {
                size: A4;
                margin: 6mm 8mm;
            }
            @page a5page {
                size: A4;
                margin: 6mm 8mm;
            }
        }
    </style>

    @php
        $saleItemChunksCount = $saleItemChunks->count();
        $overallSubtotal = 0;
        $saleItemChunksCount = $saleItemChunks->count();
    @endphp
    @foreach($saleItemChunks as $chunkData)
        @php
            $saleItems = $chunkData->items;
            $chunkLength = $chunkData->chunkLength;
        @endphp
        <table class="page-break main-table" style="width: 100%; border-collapse: collapse; page: {{ $chunkData->pageName }};">
            <thead>
                <tr>
                    <th colspan="5" style="font-size: 24px; font-weight: bolder; text-align: left;">
                        {{ $companyProfile['company_name'] ?? '' }}
                    </th>
                    <th colspan="3" style="text-align: right; font-size: small; font-weight: normal;">{{ $saleItemChunksCount > 1 ? "Halaman: $loop->iteration/$saleItemChunksCount" : '' }}</th>
                </tr>
                <tr>
                    <td colspan="8" style="padding-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between;">
                            <div>
                                Pelanggan:
                                <table>
                                    <tr>
                                        <td style="padding: 0; width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-bottom: 1px solid #000;">{{ $sale->customer_name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0; width: 200px; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border-bottom: 1px solid #000;">{{ $sale->customer_address }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div style="text-align: right;">
                                Penjualan:
                                <table style="text-align: right;">
                                    <tr>
                                        <td style="padding: 0; width: 200px; border-bottom: 1px solid #000;">{{ $sale->series_number }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0; width: 200px; border-bottom: 1px solid #000;">{{ \Carbon\Carbon::today()->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="border text-center" rowspan="2" style="width: 30px;">No.</td>
                    <td class="border" rowspan="2" style="width: 310px;">Nama Barang</td>
                    <td class="border text-center" rowspan="2" style="width: 35px;">Qty.</td>
                    <td class="border text-center" rowspan="2" style="width: 70px;">@</td>
                    <td class="border text-center" rowspan="2" style="width: 85px;">Harga</td>
                    <td class="border text-center" colspan="2" style="width: 100px;">Diskon</td>
                    <td class="border text-center" rowspan="2" style="width: 90px;">Total</td>
                </tr>
                <tr>
                    <td class="border text-center" style="width: 25px;">%</td>
                    <td class="border text-center" style="width: 75px;">Rp</td>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandtotal = 0;
                    $saleItemsCount = $saleItems->count();
                @endphp

                @foreach($saleItems as $item)
                    @php
                        $total = $item->quantity * $item->price;
                        $discountPercentageInNominal = $total * ($item->discount_percentage / 100);
                        $total = $total - $discountPercentageInNominal - $item->discount_nominal;
                        $grandtotal += $total;
                    @endphp
                    <tr>
                        <td class=" text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class=" text-right">{{ decimal_number_format($item->quantity) }}</td>
                        <td class=" text-center">{{ $item->uom }}</td>
                        <td class=" text-right">{{ decimal_number_format($item->price) }}</td>
                        <td class=" text-right">{{ $item->discount_percentage }}</td>
                        <td class=" text-right">{{ decimal_number_format($item->discount_nominal) }}</td>
                        <td class=" text-right">{{ decimal_number_format($total) }}</td>
                    </tr>
                @endforeach
                @php
                    $overallSubtotal += $grandtotal;
                @endphp

                @for ($i = 0; $i < $chunkLength - $saleItemsCount; $i++)
                    <tr>
                        <td style="height: 22px;"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor

                <tr style="border-top: 1px dotted #000;">
                    <td colspan="4" rowspan="5" style="vertical-align: top;">
                        <div style="display: flex;">
                            <div class="text-center" style="width: 110px; height: 75px; border-bottom: 1px solid #000;">Hormat Kami,</div>
                            <div style="width: 50px;"></div>
                            <div class="text-center" style="width: 110px; height: 75px; border-bottom: 1px solid #000;">Pelanggan,</div>
                        </div>
                    </td>
                    <td colspan="4" style="vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tbody>
                            @if($loop->last)
                                <tr>
                                    <td style="padding: 0; width: 150px;">Subtotal</td>
                                    <td class="text-right" style="padding: 0; width: 125px;"><span style="float: left;">Rp</span>{{ decimal_number_format($overallSubtotal) }}</td>
                                </tr>
                                <tr>
                                    @php
                                        $discountPercentageInNominal = $overallSubtotal * (($sale->discount_percentage ?? 0) / 100);
                                    @endphp
                                    <td style="padding: 0;">Diskon {{ $sale->discount_percentage ? "($sale->discount_percentage %)" : '%' }}</td>
                                    <td class="text-right" style="padding: 0;"><span style="float: left;">Rp</span>{{ decimal_number_format($discountPercentageInNominal) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0;">Diskon Rp</td>
                                    <td class="text-right" style="padding: 0;"><span style="float: left;">Rp</span>{{ decimal_number_format($sale->discount_nominal ?? 0) }}</td>
                                </tr>
                                <tr>
                                    @php
                                        $finalTotal = $overallSubtotal - $discountPercentageInNominal - ($sale->discount_nominal ?? 0);
                                        $taxPercentageInNominal = $finalTotal * (($sale->tax_percentage ?? 0) / 100);
                                        $finalTotal += $taxPercentageInNominal;
                                    @endphp
                                    <td style="padding: 0;">Pajak {{ $sale->tax_percentage ? "($sale->tax_percentage %)" : '' }}</td>
                                    <td class="text-right" style="padding: 0;"><span style="float: left;">Rp</span>{{ decimal_number_format($taxPercentageInNominal) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bolder; padding: 0;">Grand Total</td>
                                    <td class="text-right" style="font-weight: bolder; padding: 0;"><span style="float: left;">Rp</span>{{ decimal_number_format($finalTotal) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td style="padding: 0; width: 150px;">Subtotal</td>
                                    <td class="text-right" style="padding: 0; width: 125px;"><span style="float: left;">Rp</span>{{ decimal_number_format($grandtotal) }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    @endforeach
</div>
