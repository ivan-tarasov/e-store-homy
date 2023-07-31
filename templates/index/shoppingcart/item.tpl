<li>
   <div class="basket-item">
      <div class="row">
         <div class="col-xs-6 col-sm-4 no-margin text-center">
            <div class="thumb">
               <img alt="" src="{item_img}" />
            </div>
         </div>
         <div class="col-xs-8 col-sm-8 no-margin">
            <div class="title"><b>{item_brand}</b> {item_name}</div>
            <div class="price">{item_cost}</div>
            <div class="title"><span id="item-qty{item_id}">{item_qty}</span> шт.</div>
         </div>
      </div>
      <form method="POST" action="">
         <input type="hidden" name="del[id]" value="{item_id}" />
         <button id="addto-cart" type="submit" class="btn close-btn btn-xs btn-link"></button>
      </form>
   </div>
</li>