<div class="row no-margin cart-item" id="item-{item_id}">

   <!-- 1 -->
   <div class="col-xs-2 col-sm-1 no-margin">
      <a href="#" class="thumb-holder">
         <img class="lazy" alt="" src="{item_img}">
      </a>
   </div>

   <!-- 2 -->
   <div class="col-xs-4 col-sm-4 ">
      <div class="title">
         <a href="{url}">{item_name}</a>
      </div>
      <div class="brand">Код: {item_id}</div>
   </div>

   <!-- 3 -->
   <div class="col-xs-3 col-sm-4 no-margin">
      <div class="quantity pull-right">
         <div class="item-cost hidden-xs">{item_cost}</div>
         <div class="betweener x hidden-xs">x</div>
         <div class="le-quantity">
            <!--form method="POST"-->
               <!--input type="hidden" name="add[id]" value="{item_id}" /-->
               <button class="btn btn-default btn-lg minus" id="updMin" data-options='{"action":"reduce","id":{item_id}}'></button>
               <input name="add[qty]" id="qty-{item_id}" readonly="readonly" type="text" value="{item_qty}" />
               <button class="btn btn-default btn-lg plus" id="updPls" data-options='{"action":"plus","id":{item_id}}'></button>
            <!--/form-->
         </div>
         <div class="betweener eq">=</div>
      </div>
   </div>

   <!-- 4 -->
   <div class="col-xs-3 col-sm-3 no-margin">
      <div class="price">
         <div id="cost{item_id}">{item_cost_total}</div>
      </div>
      <button class="btn close-btn btn-xs" id="delete" data-options='{"action":"delete","id":{item_id}}'></button>
   </div>

   <div class="col-md-6 label label-danger pull-right" id="message-{item_id}"></div>

</div>
<div class="row no-margin cart-item hide" id="undo-div-{item_id}">
   <div class="alert alert-danger text-center" role="alert">
      Позиция <a href="{url}" class="alert-link">{item_name}</a> будет удалена из корзины.
      Это действие можно <button class="btn btn-default" id="undo" data-options='{"action":"undo","id":{item_id}}'>отменить</button>
   </div>
</div>
