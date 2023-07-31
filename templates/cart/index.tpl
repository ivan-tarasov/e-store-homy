<section id="cart-page">
   <div class="container">
      <div class="page-header">
         <h2 class="page-title">Корзина покупок</h2>
      </div>

      <script>
      $(document).ready(function(){

         var deleteTimer = [];

         function deleting(id,url) {
            $('#undo-div-' + id).animate({opacity:0}, 'slow', function() {
               $('#item-' + id).hide();
               $('#undo-div-' + id).hide();
               console.log('deleting(): '+id);
            });
            if (url != null) {
               location.href = url;
            }
         }

         $('#updMin,#updPls,#delete,#undo').on('click', function() {

            var id = $(this).data('options').id;
            var action = $(this).data('options').action;

            if (action == 'undo') {
               clearTimeout(deleteTimer[id]);
               $('#undo-div-' + id).addClass('hide');
               $('#item-' + id).removeClass('hide');
               action = 'plus';
            }

            $('#cost' + id).html('<i class="fa fa-lg fa-pulse fa-spinner"></i>');

            $.post('/class/ajax/shoppingcart.ajax.php', { id:id, action:action }, function(data) {
               var response = jQuery.parseJSON(data);

               $('#cost' + id).html(response.price);
               $('#sub-total*').html(response.subtotal);
               $('#shipping').html(response.shipping);
               $('#total-value').html(response.totalvalue);
               $('#total-qty').html(response.totalqty);
               $('#item-qty' + id).html(response.itemqty);
               $('#qty-' + id).val(response.qty);
               $('#message-' + id).addClass('hide');
               $('#message-' + id).html(response.message).removeClass('hide');

               if (response.deleted) {
                  $('#undo-div-' + id).removeClass('hide');
                  $('#item-' + id).addClass('hide');

                  if (response.redirect) {
                     redirectURL = response.redirect;
                  } else {
                     redirectURL = null;
                  }
                  deleteTimer[id] = setTimeout(deleting.bind(null,id,redirectURL), 5000);;
               }
            }).fail(function() {
               alert( "Обновление не удалось" );
            });

            return false;

         });

      });
      </script>
      <div id="summary"></div>
      <div class="col-xs-12 col-md-9 items-holder no-margin">

         {list-item}

      </div>

      <div class="col-xs-12 col-md-3 no-margin sidebar">
         <div class="widget cart-summary">
            <h1 class="border">Корзина покупок</h1>
            <div class="body">

               <ul class="tabled-data no-border inverse-bold">
                  <li>
                     <label>Подытог</label>
                     <div class="value pull-right" id="sub-total">{sub_total_value}</div>
                  </li>
                  <li>
                     <label>Доставка</label>
                     <div class="value pull-right" id="shipping">{shipping_value}</div>
                  </li>
               </ul>

               <ul id="total-price" class="tabled-data inverse-bold no-border">
                  <li>
                     <label>Итого</label>
                     <div class="value pull-right" id="total-value">{total_value}</div>
                  </li>
               </ul>

               <div class="buttons-holder">
                  <a class="le-button big" href="/checkout/">Оформить</a>
                  <a class="simple-link block" href="/">продолжить покупки</a>
               </div>

            </div>
         </div>

         <div id="cupon-widget" class="widget">
            <h1 class="border">Скидка</h1>
            <div class="body">
               <form>
                  <div class="inline-input">
                     <input data-placeholder="код купона на скидку" type="text" class="placeholder" />
                     <button class="le-button" type="submit">OK</button>
                  </div>
               </form>
            </div>
         </div>

      </div>

   </div>
</section>
