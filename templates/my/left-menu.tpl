<section class="sidebar-page">
   <div class="container">

      <div class="col-xs-12 col-sm-3 no-margin sidebar narrow">
         
         <div class="widget">
    
            <div class="body">
               <ul class="le-links">
                  <li><a href="/my/">Мой профиль</a></li>
                  <li><a href="/my/personal/">Личные данные</a></li>
                  <li><a href="/my/orders/">Мои заказы <span class="badge">{count_orders}</span></a></li>
                  <li><a href="#">Я смотрел</a></li>
                  <li><a href="#">Избранное <span class="badge"></span></a></li>
                  <li><a href="#">Бонусные баллы <span class="badge"></span></a></li>
               </ul>
            </div>
            
            <hr />
            <div class="body">
               <ul class="le-links">
                  <li><a href="/my/pass/">Смена пароля</a></li>
                  <li><a href="#" role="menuitem" id="logout">Выход</a></li>
               </ul>
            </div>
            
         </div>
         <script>
         $(function(){
            var getLocation = function(href) {
               var l = document.createElement("a");
               l.href = href;
               return l;
            };
            var l = getLocation(window.location.href);
            $('.le-links a[href="'+l.pathname+'"]').addClass('active');
         });
         </script>

      </div>

      <div class="col-xs-12 col-sm-9 no-margin wide sidebar page-main-content">
