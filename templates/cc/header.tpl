<!DOCTYPE html>
<html lang="ru">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Панель администрирования / homy.su</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bower/metisMenu/dist/metisMenu.min.css" rel="stylesheet">
    <link href="/css/cc/timeline.css" rel="stylesheet">
	 <link href="/css/cc/tabs.css" rel="stylesheet">
    <link href="/css/cc/style.css" rel="stylesheet">
    <link href="/css/cc/sb-admin-2.css" rel="stylesheet">
    <link href="/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="/css/sweetalert2.min.css" rel="stylesheet" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="/js/jquery-1.11.2.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function(){

      $('a[rel=popover]').popover({
         html      : true,
         trigger   : 'hover',
         placement : 'right',
         template  : '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content" style="width: 225px; height: 225px;"></div></div>',
         content: function(){
            $(this).css('cursor', 'pointer');
            return '<img src="'+$(this).data('img') + '" />';
         }
});

    });
    </script>

</head>

<body>

   <div id="wrapper">

      <!-- Navigation -->
      <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
         <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
               <span class="sr-only">Навигация</span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
               <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/cc/">
               Панель администрирования (homy.su)
            </a>
         </div><!-- /.navbar-header -->
