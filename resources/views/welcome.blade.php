<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Google APIs</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <style>
            /* Insert the background CSS here */
            body {
                margin: 0;
                padding: 0;
                height: 100vh;
                background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
                background-size: 400% 400%;
                animation: animatedBackground 15s ease infinite;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                flex-direction: column;
            }

            @keyframes animatedBackground {
                0% {
                    background-position: 0% 50%;
                }
                50% {
                    background-position: 100% 50%;
                }
                100% {
                    background-position: 0% 50%;
                }
            }

            h1 {
                font-size: 4rem;
                color: #fff8e1;
                text-align: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                text-shadow:
                    0 0 10px #ffc107,
                    0 0 20px #ff5722,
                    0 0 40px #ff4081,
                    0 0 60px #ff6d00,
                    0 0 80px #f50057;
                animation: pulseWarmGlow 2.5s ease-in-out infinite;
            }

            p {
                font-size: 1.5rem;
                color: #fffbe6;
                text-align: center;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                text-shadow:
                    0 0 6px #ffd54f,
                    0 0 12px #ff7043,
                    0 0 24px #ff4081,
                    0 0 36px #ff3d00;
                animation: pulseWarmGlow 3s ease-in-out infinite;
            }

            @keyframes pulseWarmGlow {
                0%, 100% {
                    text-shadow:
                    0 0 10px #ffc107,
                    0 0 20px #ff5722,
                    0 0 40px #ff4081,
                    0 0 60px #ff6d00,
                    0 0 80px #f50057;
                }
                50% {
                    text-shadow:
                    0 0 20px #ffe082,
                    0 0 40px #ff7043,
                    0 0 60px #ff4081,
                    0 0 80px #ff1744,
                    0 0 100px #ff4081;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>Google - APIs</h1>
                    <p>By Arindam Roy</p>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
