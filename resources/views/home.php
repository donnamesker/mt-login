<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title ?? 'My Application') ?></title>

    <link
        href="/assets/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="/assets/css/app.css"
        rel="stylesheet"
    >
</head>

<body>

    <main class="container py-5">

        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card shadow-sm">
                    <div class="card-body p-5">

                        <h1 class="display-5 mb-3">
                            My Application
                        </h1>

                        <p class="lead">
                            The application foundation is working.
                        </p>

                        <button class="btn btn-primary">
                            Bootstrap is working
                        </button>

                    </div>
                </div>

            </div>
        </div>

    </main>

    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>

</body>
</html>