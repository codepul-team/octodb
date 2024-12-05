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

        .loading {
            position: fixed;
            z-index: 999;
            height: 2em;
            width: 2em;
            overflow: show;
            margin: auto;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }

        /* Transparent Overlay */
        .loading:before {
            content: '';
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));

            background: -webkit-radial-gradient(rgba(20, 20, 20, .8), rgba(0, 0, 0, .8));
        }

        /* :not(:required) hides these rules from IE9 and below */
        .loading:not(:required) {
            /* hide "loading..." text */
            font: 0/0 a;
            color: transparent;
            text-shadow: none;
            background-color: transparent;
            border: 0;
        }

        .loading:not(:required):after {
            content: '';
            display: block;
            font-size: 10px;
            width: 1em;
            height: 1em;
            margin-top: -0.5em;
            -webkit-animation: spinner 150ms infinite linear;
            -moz-animation: spinner 150ms infinite linear;
            -ms-animation: spinner 150ms infinite linear;
            -o-animation: spinner 150ms infinite linear;
            animation: spinner 150ms infinite linear;
            border-radius: 0.5em;
            -webkit-box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
            box-shadow: rgba(255, 255, 255, 0.75) 1.5em 0 0 0, rgba(255, 255, 255, 0.75) 1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) 0 1.5em 0 0, rgba(255, 255, 255, 0.75) -1.1em 1.1em 0 0, rgba(255, 255, 255, 0.75) -1.5em 0 0 0, rgba(255, 255, 255, 0.75) -1.1em -1.1em 0 0, rgba(255, 255, 255, 0.75) 0 -1.5em 0 0, rgba(255, 255, 255, 0.75) 1.1em -1.1em 0 0;
        }

        /* Animation */

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @-moz-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @-o-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
                -moz-transform: rotate(0deg);
                -ms-transform: rotate(0deg);
                -o-transform: rotate(0deg);
                transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
                -moz-transform: rotate(360deg);
                -ms-transform: rotate(360deg);
                -o-transform: rotate(360deg);
                transform: rotate(360deg);
            }
        }
        th {
            white-space: nowrap; /* Prevents text wrapping */
            text-align: center;  /* Centers the text */
            vertical-align: middle; /* Aligns text vertically */
        }
        th {
            border: 2px solid black; /* Black border for column separation */
        }

        th:last-child {
            border-right: none; /* Remove border from the last column */
        }
    </style>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
</head>

<body style="background-color: aliceblue">
    <div class="loading" style="display: none;">Loading&#8230;</div>
    <div class="container-fluid mt-4">
            {{--<div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Query</label>
                        <input type="text" class="form-control typeahead" placeholder="Search MPN or SKU">
                    </div>
                </div>
            </div>--}}
        <div class="row justify-content-center mt-5">
            <div class="col-md-3">
                <div class="form-group position-relative">
                    <label for="query" class="form-label fw-bold">Search Query</label>
                    <input type="text" id="query" class="form-control typeahead rounded-pill px-4 shadow-sm" placeholder="Search MPN or SKU">
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table style="min-height: 200px;background-color: white" class="table table-striped table-hover">
                        <thead>
                            <tr class="table-primary sticky-top">
                                <th scope="col" class="w-auto text-nowrap">Action</th>
                                <th scope="col" class="w-auto text-nowrap">Sr.</th>
                                <th scope="col" class="w-auto text-nowrap">Query</th>
                                <th scope="col" class="w-auto text-nowrap">Qty</th>
                                <th scope="col" class="w-auto text-nowrap">Matches</th>
                                <th scope="col" class="w-auto text-nowrap">Part</th>
                                <th scope="col" class="w-auto text-nowrap">Part Description</th>
                                <th scope="col" class="w-auto text-nowrap">Description</th>
                                <th scope="col" class="w-auto text-nowrap">Schematic Reference</th>
                                <th scope="col" class="w-auto text-nowrap">Internal Part Number</th>
                                <th scope="col" class="w-auto text-nowrap">Lifecycle</th>
                                <th scope="col" class="w-auto text-nowrap">Lead Time</th>
                                <th scope="col" class="w-auto text-nowrap">RoHS</th>
                                <th scope="col" class="w-auto text-nowrap">Digi-Key</th>
                                <th scope="col" class="w-auto text-nowrap">Mouser</th>
                                <th scope="col" class="w-auto text-nowrap">Newark</th>
                                <th scope="col" class="w-auto text-nowrap">Onlinecomponent</th>
                                <th scope="col" class="w-auto text-nowrap">RS Components</th>
                                <th scope="col" class="w-auto text-nowrap">Distributor/SKU</th>
                                <th scope="col" class="w-auto text-nowrap">Unit Price</th>
                                <th scope="col" class="w-auto text-nowrap">Line Total</th>
                                <th scope="col" class="w-auto text-nowrap">Batch Total</th>
                                <th scope="col" class="w-auto text-nowrap">Notes</th>
                            </tr>
                        </thead>
                        <tbody id="dom-details"></tbody>
                    </table>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <button id="submit" class="btn btn-primary px-5 py-3 fw-bold fs-5">
                        Save Data
                    </button>
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
                $(".loading").show();
                // Check for duplicate entries
                if ($(`#dom-details tr:contains(${selection.mpn})`).length > 0) {
                    alert("This item is already added!");
                    $(".loading").hide();
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
                checkExit(selection.mpn)
                    .then((exists) => {
                        console.log(exists ? 'Exists' : 'Does not exist');
                        // Append selected data to the table
                        const selectedData = `
                            <tr>
                                <td>
                                    <a class="text-danger remove-row" style="margin-right:10px;">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <input type="checkbox" ${exists ? 'checked' : ''} class="row-checkbox">
                                </td>
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
                                <td>Price:${digi_key_price}, Stock:${digi_key_stock}, ${digi_key_packing}</td>
                                <td>Price:${mouser_price}, Stock:${mouser_stock}, ${mouser_packing}</td>
                                <td>Price:${newark_price}, Stock:${newark_stock}, ${newark_packing}</td>
                                <td>Price:${online_component_price}, Stock:${online_component_stock}, ${online_component_packing}</td>
                                <td>-</td>
                                <td>${digi_key}</td>
                                <td>${digi_key_price}</td>
                                <td>${digi_key_price}</td>
                                <td>${digi_key_price}</td>
                                <td><input type="text" value="" name="note" id="note"></td>
                            </tr>
                        `;
                        $('#dom-details').append(selectedData);

                        $(".loading").hide();
                    })
                    .catch((err) => {
                        console.error('Error checking existence:', err);
                    });


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

        function checkExit(query) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: "{{ url('bom/exit') }}/" + query,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(data) {
                        resolve(data.Data); // Resolve the promise with the returned data
                    },
                    error: function(err) {
                        reject(err); // Reject the promise on error
                    }
                });
            });
        }


        $("body").on("click", "#submit", function(e) {
            e.preventDefault();

            $("#submit").prop("disabled", true);
            $(".loading").show();
            // Validation logic

            if ($('#dom-details tr').length == 0) {
                alert("Add row first!");
                $(".loading").hide();
                return false;
            }

            var productData = [];

            $('#dom-details tr').each(function() {
                let isChecked = $(this).find('.row-checkbox').is(':checked');
                console.log(isChecked);
                if (isChecked) {

                    productData.push({
                        query: $(this).find('td:eq(2)').text(),
                        qty: $(this).find('#qty').val(),
                        is_match: 1,
                        part: $(this).find('td:eq(5)').text(),
                        part_description: $(this).find('td:eq(6)').text(),
                        description: $(this).find('#description').val(),
                        schematic_reference: $(this).find('#schematic_reference').val(),
                        internal_part_no: $(this).find('#internal_part_no').val(),
                        lifecycle: $(this).find('td:eq(10)').text(),
                        lead_time: $(this).find('td:eq(11)').text(),
                        rohs: $(this).find('td:eq(12)').text(),
                        digi_key: $(this).find('td:eq(13)').text(),
                        mouser: $(this).find('td:eq(14)').text(),
                        newark: $(this).find('td:eq(15)').text(),
                        online_component: $(this).find('td:eq(16)').text(),
                        rs_component: $(this).find('td:eq(17)').text(),
                        distributor: $(this).find('td:eq(18)').text(),
                        unit_price: $(this).find('td:eq(19)').text(),
                        line_total: $(this).find('td:eq(20)').text(),
                        bacth_total: $(this).find('td:eq(21)').text(),
                        note: $(this).find('#note').val()
                    });
                }
            });
            if (productData.length == 0) {
                alert('Select row first!');
            }
            console.log(productData);
            $.ajax({
                url: "{{url('bom/store')}}", // Laravel route
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: JSON.stringify(productData),
                processData: true,
                contentType: 'application/json',
                dataType: "json",
                success: function(data) {
                    if (data.Success) {
                        alert(data.Message);
                        $(".loading").hide();
                        $("#submit").prop("disabled", false);
                        $('#dom-details').empty();
                    } else {
                        alert(data.Message);
                        $(".loading").hide();
                        $("#submit").prop("disabled", false);
                    }
                },
                error: function(xhr, status, e) {
                    alert("An error occurred:");
                    $("#submit").prop("disabled", false);
                    $(".loading").hide();
                },
            });
        });
    </script>

</body>

</html>
