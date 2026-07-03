<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        * {
            font-family: "Calibri"
        }
        @media print {
            body{
                margin: 0px;
            }
            .card{
                margin: 20px;
                height:8in;
                width: 13in;
                font-size: 8px;
            }
		}
        .card{
            font-size: 9.5px;
            margin: 20pxl
        }

        .col {
			float: left;
			width: 47%;
			padding: 5px;
			margin-left: 7px;
			/*border:  solid 1px black;*/
		}

		.row:after {
			content: "";
			display: table;
			clear: both;
		}
        table.table-bordered{
            border-collapse: collapse;
        }
        table.table-bordered td{
            border: solid 0.5px #cccccc;
            
        }

        table.table-bordered th {
            border: solid 0.5px #cccccc;
            
        }
        table.table-bordered tr {
            border: solid 0.5px #cccccc;
            
        }
        table{
            margin-bottom: 10px;
            width:  100%;
        }
        
    </style>
	<link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="row">
        <div class="col">
            <div class="card"></div>
        </div>
    </div>
</body>
</html>