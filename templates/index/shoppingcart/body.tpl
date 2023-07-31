<div class="top-cart-holder dropdown animate-dropdown">

   <div class="basket">
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
         <div class="basket-item-count">
            <span class="count" id="total-qty">{total_items}</span>
            <img src="/img/icon-cart.png" alt="" />
         </div>

         <div class="total-price-basket">
            <span class="lbl">Корзина:</span>
            <span class="total-price">
               <span class="value" id="sub-total">{total_value}</span>
            </span>
         </div>
      </a>

      <ul class="dropdown-menu">
         <div class="scroll-div" id="cartUp">
            
            {top_cart_items}
         
         </div>

         <li class="checkout">
            <div class="basket-item">
               <div class="row">
                  <div class="col-sm-12">
                     <a href="/cart/" class="le-button btn btn-sm btn-block">
                        Перейти в корзину
                        <i class="fa fa-arrow-right"></i>
                     </a>
                  </div>
               </div>
            </div>
            <div class="basket-item">
               <div class="row">
                  <div class="col-xs-12 col-sm-12">
                     <a href="/checkout/" class="le-button btn btn-block">Оформить заказ</a>
                  </div>
               </div>
            </div>
         </li>

      </ul>
   </div>
</div>
