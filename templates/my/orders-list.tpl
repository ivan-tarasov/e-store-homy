<div class="panel panel-default">
   <div class="panel-heading" role="tab" id="heading-{id}">
      <h4 class="panel-title">
         <a data-toggle="collapse" data-parent="#accordion" href="#collapse-{id}" aria-expanded="false" aria-controls="collapse-{id}">
            #{id}
            <span class="badge">{cart_count}</span>
            <span class="pull-right">
               {status}
            </span>
         </a>
      </h4>
   </div>
   <div id="collapse-{id}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-{id}">
      <div class="panel-body">
         <div class="row">
            
            <div class="col-xs-12 col-sm-3">3</div>
            <div class="col-xs-12 col-sm-5">5</div>
            <div class="col-xs-12 col-sm-2">2</div>
            <div class="col-xs-12 col-sm-2">{cart_count}</div>
               
         </div>
      </div>
   </div>
</div>







<!--div class="product-item product-item-holder">
   <div class="row">
      <div class="ribbon order-status status-{status}"><span></span></div>
      <div class="col-xs-12 col-sm-1 order-id"></div>
      <div class="col-xs-12 col-sm-7">
         <div class="body">
            <div class="title">
               <a href="/my/order/id/20150202-134987">Заказ {id} от {date}</a>
            </div>
            <div class="note">Ожидаем позиции на нашем складе. Обо всех изменениях Вы будите проинформированы по email и смс.</div>
         </div>
      </div>
      <div class="col-xs-12 col-sm-2 status text-center">
         {cart_count}
      </div>
      <div class="col-xs-12 col-sm-2 price-area">
         <div class="price-current pull-right">1 190 руб.</div>
      </div>
   </div>   
</div-->
