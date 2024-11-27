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
            width: 100% !important; /* Ensure dropdown spans full width of the input */
            max-height: 300px; /* Limit height for long lists */
            overflow-y: auto; /* Add scrollbar if items exceed max-height */
            z-index: 9999; /* Ensure dropdown is on top of other elements */
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
                items: 10, // Maximum suggestions shown
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
                                    id: item.part.mpn,
                                    name: item.part.mpn,
                                    price: item.part.price || "N/A",
                                    quantity: item.part.quantity || 0
                                }));
                                asyncResults(results);
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
                }
            }).on('typeahead:select', function(event, selection) {
                // Check for duplicate entries
                if ($(`#dom-details tr:contains(${selection.id})`).length > 0) {
                    alert("This item is already added!");
                    return;
                }

                // Append selected data to the table
                const selectedData = `
                <tr>
                    <td><button class="btn btn-danger btn-sm remove-row">Remove</button></td>
                    <td>${$('#dom-details tr').length + 1}</td>
                    <td>${selection.name}</td>
                    <td>${selection.quantity}</td>
                    <td></td>
                    <td></td>
                    <td>${selection.name}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>${selection.price}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            `;
                $('#dom-details').append(selectedData);
            });

            // Remove row functionality
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>

</body>

</html>
