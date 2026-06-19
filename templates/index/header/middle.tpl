<header>
   <div class="container">
      <div class="row align-items-center">

      <div class="col-12 col-md-3 logo-holder">
         <div class="logo">
            <a href="/">
               <img alt="{logo_alt}" title="{logo_alt}" src="/img/banner-02-sm2.png" width="233" height="54" />
            </a>
         </div>
      </div>

      <div class="col-12 col-md-7 top-search-holder no-margin">
               
         <div class="contact-row d-none d-sm-block">
            <div class="phone inline">
               <i class="fa-solid fa-phone"></i> {homy_phone}</span>
            </div>
            <div class="contact inline">
               <i class="fa-solid fa-envelope"></i> {homy_email}
            </div>
         </div>

         <div class="search-area">
            <form method="get" action="/search/">
               <div class="control-group">
                  <input id="search-form" name="q" placeholder="{search_txt}"
                     type="text" class="search-field"
                     autocomplete="off" aria-label="{{search.aria_field}}" />
                  <button class="search-button" type="submit" aria-label="{{search.aria_button}}"></button>
               </div>
            </form>
         </div>
               
      </div>

      <div class="col-12 col-md-2 top-cart-row no-margin">
         <div class="top-cart-row-container">
            
            <!--div class="wishlist-compare-holder">
               <div class="wishlist ">
                  <a href="#"><i class="fa-solid fa-heart"></i> <span class="value">---</span> </a>
               </div>
               <div class="compare">
                  <a href="#"><i class="fa-solid fa-right-left"></i> <span class="value">---</span> </a>
               </div>
            </div-->

            {top_cart}
                  
         </div>
      </div>

      </div><!-- /.row -->
   </div>

</header>
