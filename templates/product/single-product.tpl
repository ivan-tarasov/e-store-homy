<script>window.dataLayer.push({"ecommerce":{"detail":{"products":[{"id":"{1c_id}","name":"{item_name_hide}","price":{price_current_clear},"brand":"{brand}","category":"{categoryPath}"}]}}});</script>
<script type="text/javascript" src="//vk.com/js/api/openapi.js?117"></script>
<script type="text/javascript">VK.init({apiId: 5099606, onlyWidgets: true});</script>
<script>!function (d, id, did, st) {var js = d.createElement("script");js.src = "https://connect.ok.ru/connect.js";js.onload = js.onreadystatechange = function () {if (!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {if (!this.executed) {this.executed = true;setTimeout(function () {OK.CONNECT.insertShareWidget(id,did,st);}, 0);}}};d.documentElement.appendChild(js);}(document,"ok_shareWidget",document.URL,"{width:170,height:30,st:'rounded',sz:20,ck:3}");</script>

<section id="single-product">
   <div class="container">

		{promo_banner}

      <div class="no-margin col-xs-12 col-sm-6 col-md-5 gallery-holder">
         <div class="product-item-holder size-big single-product-gallery small-gallery">
            <div id="owl-single-product">

               {photo_big}

            </div>
            <div class="single-product-gallery-thumbs gallery-thumbs">
               <div id="owl-single-product-thumbnails">

                  {photo_thumb}

               </div>

               <div class="nav-holder left hidden-xs">
                  <a class="prev-btn slider-prev" data-target="#owl-single-product-thumbnails" href="#prev"></a>
               </div>

               <div class="nav-holder right hidden-xs">
                  <a class="next-btn slider-next" data-target="#owl-single-product-thumbnails" href="#next"></a>
               </div>

              </div>
          </div>
      </div>

      <div class="no-margin col-xs-12 col-sm-7 body-holder">
         <div class="body">

            <div class="availability">
               <label>Доступность:</label>
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
            <!--div class="buttons-holder">
               <a class="btn-add-to-wishlist" href="#">В избранное</a>
               <a class="btn-add-to-compare" href="#">Сравнить</a>
            </div-->

            <div class="social-row">
               <div id="vk_like" class="pull-left"></div><script type="text/javascript">VK.Widgets.Like("vk_like", {type: "button", height: 20});</script>
               <a href="https://twitter.com/share" class="twitter-share-button" data-hashtags="домашнийкурск"></a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
               <div id="ok_shareWidget" class="pull-right"></div>
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

      </div>
   </div>
</section>

<section id="single-product-tab">
   <div class="container">
      <div class="tab-holder">

         <ul class="nav nav-tabs simple" >
            <!--li><a href="#description" data-toggle="tab">Описание</a></li-->
            <li class="active"><a href="#additional-info" data-toggle="tab">Характеристики</a></li>
            <li><a href="#reviews" data-toggle="tab">Отзывы ({reviews_count})</a></li>
         </ul>

         <div class="tab-content">
            <!--div class="tab-pane" id="description">
               <p>{description}</p>
            </div-->

            <div class="tab-pane active" id="additional-info">

               {properties}

               <div class="meta-row">
                  <small>* Характеристики и внешний вид ТОВАРА могут отличаться от описанных на сайте.</small>
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

<section id="seo-text">
   <div class="container">

      <p class="text-muted small lead">
         {seo_text}
      </p>

   </div>
</section>

<div class="space20"></div>

{same_category}
