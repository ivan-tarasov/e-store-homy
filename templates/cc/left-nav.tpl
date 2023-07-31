   <div class="navbar-default sidebar" role="navigation">
      <div class="sidebar-nav navbar-collapse">
         <ul class="nav" id="side-menu">

   <!-- =============================================== SEARCH ======================================================================= -->
            <li class="sidebar-search">
               <h4 class="input-group">
                  <a href="/"><i class="fa fa-arrow-left fa-lg"></i>
                     Магазин</a>
               </h4>
            </li>
   <!-- =============================================== SEARCH : END ================================================================= -->

            <li><a href="/cc/"><i class="fa fa-dashboard"></i>
               Главная панель</a></li>
            <!--li><a href="/cc/orders/"><i class="fa fa-crosshairs fa-lg"></i>
               Заказы</a></li-->
            <li>
               <a href="#"><i class="fa fa-crosshairs fa-lg"></i>
                  Заказы
                  <kbd class="label label-warning">{orders_new}</kbd>
                  <span class="fa arrow"></span>
               </a>
               <ul class="nav nav-second-level">
                  <li><a href="/cc/orders/status/new/">Новые <kbd class="label label-warning pull-right">{orders_new}</kbd></a></li>
                  <li><a href="/cc/orders/status/inwork/">В обработке <kbd class="label label-primary pull-right">{orders_inwork}</kbd></a></li>
                  <li><a href="/cc/orders/status/error/">Закрытые с ошибкой <kbd class="label label-danger pull-right">{orders_err}</kbd></a></li>
                  <li><a href="/cc/orders/status/done/">Выполненные <kbd class="label label-success pull-right">{orders_end}</kbd></a></li>
               </ul>
            </li>
            <li><a href="/cc/priceupdate/"><i class="fa fa-retweet fa-lg"></i>
               Обновление прайс-листа</a></li>
            <li><a href="/cc/addphotos/"><i class="fa fa-camera fa-lg"></i>
               Добавление фотографий</a></li>

            <!--li>
               <a href="#"><i class="fa fa-bar-chart-o fa-fw"></i> Charts<span class="fa arrow"></span></a>
               <ul class="nav nav-second-level">
                  <li><a href="flot.html">Flot Charts</a></li>
                  <li><a href="morris.html">Morris.js Charts</a></li>
               </ul>
            </li-->
         </ul>
      </div>
   </div>
</nav>
