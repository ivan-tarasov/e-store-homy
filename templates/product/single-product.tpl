<section id="single-product">
   <div class="container">

      {promo_banner}

      <div class="row">

      <div class="col-12 col-md-5 gallery-holder">
         <div class="product-item-holder size-big single-product-gallery small-gallery">
            {gallery}
         </div>
      </div>

      <div class="col-12 col-md-7 body-holder">
         <div class="body">

            <div class="availability">
               <label>{{product.availability}}</label>
               <span class="{available_bool}available">{prod_axistence}</span>
            </div>

            <div class="title">
               <h1 class="hide">{item_name_hide}</h1>
               {item_name}
            </div>
            <div class="under-title"></div>
            <div class="buttons-holder">
               {rating}
            </div>

            <div class="excerpt">
               <p>{description}</p>
            </div>

            <div class="prices">
               <div class="price-current{no_exist_price}">{price_current}</div>
               <span class="no-exist">{no_exist_note}</span>
            </div>

            <div class="qnt-holder">
               {to_cart_button}
               {to_cart_button_clc}
            </div>

         </div>
      </div><!-- /.col body-holder -->

      </div><!-- /.row -->
   </div><!-- /.container -->
</section>

<section id="single-product-tab">
   <div class="container">
      <div class="tab-holder">

         <ul class="nav nav-tabs simple">
            <li class="nav-item"><a class="nav-link active" href="#additional-info" data-bs-toggle="tab">{{product.specs_tab}}</a></li>
            <li class="nav-item"><a class="nav-link" href="#reviews" data-bs-toggle="tab">{{product.reviews_tab}} ({reviews_count})</a></li>
         </ul>

         <div class="tab-content">
            <div class="tab-pane active" id="additional-info">
               {properties}
               <div class="meta-row">
                  <small>{{product.demo_note}}</small>
               </div>
            </div>

            <div class="tab-pane" id="reviews">
               <div class="comments">
                  {opinions}
               </div>
            </div>
         </div>

      </div>
   </div>
</section>

{same_category}
