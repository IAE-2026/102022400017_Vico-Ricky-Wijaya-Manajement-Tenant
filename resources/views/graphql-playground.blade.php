<!DOCTYPE html>
<html>
<head>
    <title>GraphQL Playground - Tenant Service</title>
    <meta charset=utf-8/>
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/css/index.css"/>
    <script src="https://cdn.jsdelivr.net/npm/graphql-playground-react/build/static/js/middleware.js"></script>
</head>
<body>
<div id="root"></div>
<script>
    window.addEventListener('load', function(event) {
        GraphQLPlayground.init(document.getElementById('root'), {
            endpoint: '/api/v1/graphql',
            headers: {
                'X-IAE-KEY': '102022400017'
            }
        })
    })
</script>
</body>
</html>
