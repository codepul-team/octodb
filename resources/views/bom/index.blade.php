<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Custom Typeahead Styles to avoid conflicts */
        .tt-menu {
            width: 100% !important;
            /* Ensure dropdown spans full width of the input */
            max-height: 300px;
            /* Limit height for long lists */
            overflow-y: auto;
            /* Add scrollbar if items exceed max-height */
            z-index: 9999;
            /* Ensure dropdown is on top of other elements */
        }

        .tt-suggestion {
            padding: 10px;
            cursor: pointer;
        }

        .tt-suggestion:hover {
            background-color: #ddd;
        }

        /* Optional: Loading spinner style */
        .typeahead.loading {
            background: url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css') center no-repeat !important;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Query</label>
                    <input type="text" class="form-control typeahead" placeholder="Search MPN or SKU">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table style="min-height: 200px;" class="table table-striped table-hover mt-3">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Sr.</th>
                                <th>Query</th>
                                <th>Qty</th>
                                <th>Matches</th>
                                <th>Part</th>
                                <th>Part Description</th>
                                <th>Description</th>
                                <th>Schematic Reference</th>
                                <th>Internal Part Number</th>
                                <th>Lifecycle</th>
                                <th>Lead Time</th>
                                <th>RoHS</th>
                                <th>Digi-Key</th>
                                <th>Mouser</th>
                                <th>Newark</th>
                                <th>Onlinecomponent</th>
                                <th>RS Components</th>
                                <th>Distributor/SKU</th>
                                <th>Unit Price</th>
                                <th>Line Total</th>
                                <th>Batch Total</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="dom-details"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typeahead.js@0.11.1/dist/typeahead.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.typeahead').typeahead({
                highlight: true, // Highlights matching words
                minLength: 3, // Start after 3 characters
                limit: 100,
            }, {
                source: function(query, syncResults, asyncResults) {
                    $.ajax({
                        url: "{{ url('bom/search-mpn') }}",
                        type: 'GET',
                        data: {
                            mpn: query
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            $('.typeahead').addClass('loading');
                        },
                        success: function(data) {
                            console.log(data.Data.data);
                            if (data && data.Data && data.Data.data && data.Data.data.supSearch) {
                                const results = data.Data.data.supSearch.results.map(item => ({
                                    id: item.part.id,
                                    part: item.part.name,
                                    mpn: item.part.mpn,
                                    part_description: item.part.shortDescription || 'No description available',
                                    lifecycle: 'Production',
                                    lead_time: item.part.estimatedFactoryLeadDays / 7 + 'w' || '0w',
                                    rohs: 'Compliant',
                                    sellers: item.part.sellers,

                                }));
                                asyncResults([results]);
                                /*alert('heel');

                                console.log('Asharab');
                                console.log(results);*/
                            } else {
                                asyncResults([]);
                            }
                        },
                        error: function() {
                            // Handle errors gracefully
                            console.error('Error fetching data');
                            asyncResults([]);
                        },
                        complete: function() {
                            $('.typeahead').removeClass('loading');
                        }
                    });
                },
                display: 'mpn',
                templates: {
                    suggestion: function(data) {
                        // Example: Assuming `data.names` is an array of multiple names
                        console.log("Suggestion Data:", data);
                        return data.map(item => {
                            return `
                                    <div>
                                        <strong>${item.mpn}</strong>
                                    </div>
                                `;
                        }).join('');
                    }
                }
            }).on('typeahead:select', function(event, selection) {
                // Check for duplicate entries
                if ($(`#dom-details tr:contains(${selection.mpn})`).length > 0) {
                    alert("This item is already added!");
                    return;
                }
                // Seller detail
                var digi_key = '';
                var digi_key_price = 0;
                var digi_key_stock = 0;
                var digi_key_packing = '';
                var mouser = '';
                var mouser_price = 0;
                var mouser_stock = 0;
                var mouser_packing = '';
                var newark = '';
                var newark_price = 0;
                var newark_stock = 0;
                var newark_packing = '';
                var online_component = '';
                var online_component_price = 0;
                var online_component_stock = 0;
                var online_component_packing = '';
                console.log("sellers", selection.sellers);
                $.each(selection.sellers, function(k, value) {
                    console.log("company", value);
                    console.log("offers", value.offers);
                    console.log("prices", value.offers.prices);
                    if (value.offers) {
                        const companyId = value.company.id || 'N/A';
                        const companyInfo = value.company.name || 'N/A';
                        const priceInfo = value.offers[0]?.prices[0]?.price || 0;
                        const stockInfo = value.offers[0].inventoryLevel || 0;
                        const packingInfo = value.offers[0].packaging || 'N/A';
                        if (companyId === '2429') {
                            online_component = companyInfo;
                            online_component_price = priceInfo;
                            online_component_stock = stockInfo;
                            online_component_packing = packingInfo;
                        }
                        if (companyId === '2401') {
                            mouser = companyInfo;
                            mouser_price = priceInfo;
                            mouser_stock = stockInfo;
                            mouser_packing = packingInfo;
                        }
                        if (companyId === '2402') {
                            newark = companyInfo;
                            newark_price = priceInfo;
                            newark_stock = stockInfo;
                            newark_packing = packingInfo;
                        }
                        if (companyId === '459') {
                            digi_key = companyInfo;
                            digi_key_price = priceInfo;
                            digi_key_stock = stockInfo;
                            digi_key_packing = packingInfo;
                        }
                    } else {
                        console.warn(`Missing offers or prices for seller ID ${companyId}`);
                    }
                });

                // Append selected data to the table
                const selectedData = `
                <tr>
                    <td><button class="btn btn-danger btn-sm remove-row">Remove</button></td>
                    <td>${$('#dom-details tr').length + 1}</td>
                    <td>${selection.mpn}</td>
                    <td><input type="number" value="1" name="qty" id="qty"></td>
                    <td>Yes</td>
                    <td>${selection.part}</td>
                    <td>${selection.part_description}</td>
                    <td><input type="text" value="" name="description" id="description"></td>
                    <td><input type="text" value="" name="schematic_reference" id="schematic_reference"></td>
                    <td><input type="text" value="" name="internal_part_no" id="internal_part_no"></td>
                    <td>${selection.lifecycle}</td>
                    <td>${selection.lead_time}</td>
                    <td>${selection.rohs}</td>
                    <td>Price:${digi_key_price}, Stock:${digi_key_stock}, ${digi_key_packing} </td>
                    <td>Price:${mouser_price}, Stock:${mouser_stock}, ${mouser_packing} </td>
                    <td>Price:${newark_price}, Stock:${newark_stock}, ${newark_packing} </td>
                    <td>Price:${online_component_price}, Stock:${online_component_stock}, ${online_component_packing} </td>
                    <td>-</td>
                    <td>${digi_key}</td>
                    <td>${digi_key_price}</td>
                    <td>${digi_key_price}</td>
                    <td>${digi_key_price}</td>
                    <td><input type="text" value="" name="note" id="note"></td>
                </tr>
            `;
                $('#dom-details').append(selectedData);
            });

            // Remove row functionality
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });

        function getData(query) {
            $.ajax({
                url: "{{ url('bom/get-data') }}",
                type: 'GET',
                data: {
                    mpn: query
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                beforeSend: function() {
                    $('.typeahead').addClass('loading');
                },
                success: function(data) {
                    var data = data.Data;
                    console.log("getData", data);
                }
            });
        }
    </script>

</body>

</html>