<div class="product-item product-item-holder">
   <!--div class="ribbon blue"><span>#{prod_code}</span></div-->

   <div class="row">

      <a href="{prod_url}">
         <div class="no-margin col-xs-12 col-sm-4 image-holder">
            <div class="image">
               <img alt="{prod_name}" title="{prod_name}" src="{img_path}" width="200" />
            </div>
         </div>
      </a>

      <div class="no-margin col-xs-12 col-sm-5 body-holder">
         <div class="body">
            <div class="title">
               <a href="{prod_url}">
                  {prod_name}
                  {rating}
               </a>
            </div>
            <div class="brand">
               {prod_brand}
            </div>
            <div class="excerpt">
               <p>
                  {prod_description}
               </p>
            </div>
            <div class="no-margin">
               <ul class="list-inline">
                  <li><a class="btn-add-to-wishlist" href="#">В желаемое</a></li>
                  <li><a class="btn-add-to-compare" href="#">Сравнить</a></li>
               </ul>
            </div>
         </div>
      </div>

      <div class="no-margin col-xs-12 col-sm-3 price-area">
         <div class="right-clmn">
            <div class="price-current">{prod_price}</div>
            <!--div class="price-prev">1 399 руб.</div-->
            <div class="availability">
               <label>Наличие:</label>
               <span class="{available_bool}available">{prod_axistence}</span>
            </div>
            <form method="POST" action="">
               <input type="hidden" name="add[id]" value="{prod_code}" />
               {to_cart_button}
            </form>
         </div>
      </div>
   </div>
</div>
