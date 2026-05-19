<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{__('Error Page')}}</title>
</head>
<body style="
    min-height: calc(100vh - 16px);
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #F2F2F2;
">
    <div>
        <div style="
                background-color: #fff;
                text-align: center;
                max-width: 860px;
                width: 100%;
                margin: 0 auto;
                border-radius: 10px;
                padding: 50px;
            ">
            <h2 style="
                font-size: 100px;
                font-weight: 400;
                line-height: 121px;
                margin: 0;
            ">{{$exception->getStatusCode()}}</h2>
            <p style="
                font-size: 24px;
                font-weight: 400;
                line-height: 29px;
                margin-top: 10px;
                word-break: break-word;
            ">{{$exception->getMessage()}}</p>
        </div>
    </div>
</body>
</html>