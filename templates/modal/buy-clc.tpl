<div class="modal fade text-left" id="buy-clc" tabindex="-1" role="dialog" aria-labelledby="ofertaLabel">
   <div class="modal-dialog" role="document">
      <div class="modal-content">

         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">&times;</span></button>
            <h3 class="modal-title" id="ofertaLabel">Оформление заказа</h3>
         </div>

         <form id="buy-clc-form" method="post">

            <div class="modal-body small">
               <div class="row">
                  <div class="col-sm-8 col-xs-12 form-group">
                     <label for="buy-clc-name" class="control-label">Ваше имя:</label>
                     <input type="text" class="form-control" id="buy-clc-name" name="name" placeholder="Как к Вам обращаться?">
                  </div>
                  <div class="col-sm-4 col-xs-12 form-group">
                     <label for="buy-clc-phone" class="control-label">Контактный телефон:</label>
                     <input type="text" class="form-control" id="buy-clc-phone" name="phone" placeholder="+7 (910) 000-0000">
                  </div>
                  <div class="col-xs-12 form-group">
                     <label for="buy-clc-name" class="control-label">Адрес доставки <small class="text-muted">(по желанию)</small>:</label>
                     <input type="text" class="form-control" id="buy-clc-name" name="name" placeholder="г. Курск, ул. Ленина, д. 1">
                  </div>
                  <div class="col-xs-12 form-group">
                     <label for="buy-clc-message" class="control-label">Комментарий к заказу:</label>
                     <textarea class="form-control" id="buy-clc-message" name="message" rows="3" placeholder="Особые пожелания"></textarea>
                  </div>
                  <div class="col-xs-12 form-group">{terms}</div>
                  <input type="hidden" id="buy-clc-id" value="{id}" />
               </div>
            </div>

            <div class="modal-footer">
               <div class="col-md-6">
                  {captcha}
               </div>
               <div class="col-md-6">
                  <button type="submit" class="le-button big" id="buy-clc-submit">Оформить заказ</button>
               </div>
            </div>
         </form>

      </div>
   </div>
</div>
<button id="buy-clc-{id}" class="le-button{size}{class} hvr-icon-buzz-out" data-toggle="modal" data-target="#buy-clc">{text}</button>
