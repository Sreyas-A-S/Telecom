<!DOCTYPE html>
<html>
<head>
    <title>Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.29.0/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        /* Custom CSS to reposition the Authorize button */
        .swagger-ui .topbar-wrapper {
            display: flex;
            justify-content: space-between; /* Pushes items to ends */
            align-items: center;
            padding: 10px 20px; /* Add some padding */
        }
        .swagger-ui .topbar-wrapper .topbar-console-fetch {
            order: 2; /* Move the authorize button to the right */
        }
        .swagger-ui .topbar-wrapper .link {
            order: 1; /* Keep the logo/title to the left */
        }
    </style>
</head>
<body>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.29.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.29.0/swagger-ui-standalone-preset.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif
        });

        window.onload = function() {
            // Begin Swagger UI call
            const ui = SwaggerUIBundle({
                url: "{{ url('/docs/api-docs.json') }}", // URL to your generated OpenAPI JSON
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });

            window.ui = ui;
        };
    </script>
</body>
</html>