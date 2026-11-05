<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales by Cashier Report</title>
</head>
<body>
    @include('Report.partials.sales-by-cashier-content')

    <script>
        window.onload = function () {
            window.print();
        };
    </script>
</body>
</html>