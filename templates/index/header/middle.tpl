<header>
   <div class="container no-padding">
      
      <div class="col-xs-12 col-sm-12 col-md-3 logo-holder">
         <div class="logo">
            <a href="/">
               <img alt="{logo_alt}" title="{logo_alt}" src="/img/banner-02-sm2.png" width="233" height="54" />
            </a>
         </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-7 top-search-holder no-margin">
               
         <div class="contact-row">
            <div class="phone inline">
               <i class="fa fa-phone"></i> {homy_phone}</span>
            </div>
            <div class="contact inline">
               <i class="fa fa-envelope"></i> {homy_email}
            </div>
         </div>

         <div class="search-area">
            <form>
               <div class="control-group">
                  <input id="search-form" placeholder="{search_txt}"
                     type="text" class="search-field" 
                     onblur="if(this.value=='')this.value=this.defaultValue;" 
                     onfocus="if(this.value==this.defaultValue)this.value='';" />
                  <!--a class="search-button" href="#" ></a-->
               </div>
            </form>
         </div>
               
      </div>

      <div class="col-xs-12 col-sm-12 col-md-2 top-cart-row no-margin">
         <div class="top-cart-row-container">
            
            <!--div class="wishlist-compare-holder">
               <div class="wishlist ">
                  <a href="#"><i class="fa fa-heart"></i> <span class="value">---</span> </a>
               </div>
               <div class="compare">
                  <a href="#"><i class="fa fa-exchange"></i> <span class="value">---</span> </a>
               </div>
            </div-->

            {top_cart}
                  
         </div>
      </div>

   </div>

</header>
