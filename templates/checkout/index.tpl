<section id="checkout-page">
   <div class="container">
      <div class="col-xs-12 no-margin">
         <form role="form" class="cf-style-1" id="checkout-form">

            <div class="page-header">
               <h2 class="page-title">Оформление заказа</h2>
            </div>

            <div class="billing-address">
               <a name="checkout-form" />
               <h2 class="border h1">Адрес доставки</h2>

                  <div class="row field-row">
                     <div class="col-xs-12 col-sm-6">
                        <label class="control-label">Ваше имя<sup class="text-danger">*</sup></label>
                        <input name="lastname" id="lastname" class="le-input input-lg form-control"{value_lastname} />
                        <span class="help-block" id="lastname-help">Как к Вам обращаться?</span>
                     </div>
                     <div class="col-xs-12 col-sm-2">
                        <label class="control-label">Телефон<sup class="text-danger">*</sup></label>
                        <input name="reg-phone" id="reg-phone" class="le-input input-lg form-control"{value_phone} />
                        <span class="help-block" id="reg-phone-help"></span>
                     </div>
                     <div class="col-xs-12 col-sm-4">
                        <label>Электронная почта</label>
                        <input name="email" id="email" class="le-input input-lg form-control">
                     </div>
                  </div>

                  <div class="row field-row">
                     <div class="col-xs-12 col-sm-9">
                        <label class="control-label">Адрес доставки <small class="text-muted">(если доставка будет осуществляться курьером)</small></label>
                        <input name="address" id="address" class="le-input input-lg form-control"{value_address} />
                     </div>
                     <div class="col-xs-12 col-sm-3">
                        <label>Время доставки <small class="text-muted">(при доставке курьером)</small></label>
                        <select name="deliveryTime" class="le-input input-lg form-control">
                           <option>с 9:00 до 12:00</option>
                           <option>с 12:00 до 13:00</option>
                           <option>с 13:00 до 18:00</option>
                           <option>с 18:00 до 19:00</option>
                           <option>с 19:00 до 21:00</option>
                           <option>другое время (укажу в примечании)</option>
                        </select>
                     </div>
                  </div>

                  <div class="row field-row">

                     <div class="col-xs-12 col-sm-6">
                        <h2 class="border h1">Оплата заказа</h2>

                        <div>
                           <input class="le-checkbox big" type="radio" name="pay" value="При получении" checked /><i class="fake-box"></i>
                           <a class="simple-link bold">Наличными или банковской картой при получении товара</a>
                           <span class="text-muted">
                              - после доставки товара и проверки его работоспособности заказ можно будет оплатить как наличными, так и банковской картой.
                           </span>
                        </div>
                        <div class="space10"></div>
                        <!--div>
                           <input class="le-checkbox big" type="radio" name="pay" value="onLine" /><i class="fake-box"></i>
                           <a class="simple-link bold">Online оплата банквской картой</a>
                           - оплата происходит в режиме online. Все действия происходят на странице банка, мы не храним и не обрабатываем номера карт.
                        </div>
                        <div class="space10"></div-->
                        <div>
                           <input class="le-checkbox big" type="radio" name="pay" value="Безналичный расчет" /><i class="fake-box"></i>
                           <a class="simple-link bold">По безналичному рассчету</a>
                           - после оформления заказа можно будет распечатать счет на оплату. После оплаты заказ будет обработан в тчение 1-2 дней.
                        </div>

                     </div>
                     <div class="col-xs-12 col-sm-6">
                        <h2 class="border h1">Примечание к заказу</h2>
                        <textarea name="note" id="note" class="le-input input-lg form-control" rows="5" placeholder="Особые пожелания?"></textarea>
                     </div>

                  </div>

            </div>

            <section id="your-order">
               <h2 class="border h1">Ваш заказ</h2>

                  {order-items}

            </section>

            <div id="total-area" class="row no-margin">
               <div class="col-xs-12 col-lg-4 col-lg-offset-8 no-margin-right">
                  <div id="subtotal-holder">
                     <ul class="tabled-data inverse-bold no-border">
                        <li>
                           <label>Подытог</label>
                           <div class="value text-right">{total-value}</div>
                        </li>
                        <li>
                           <label>Доставка</label>
                           <div class="value text-right">{shipping_value}</div>
                        </li>
                     </ul>

                     <ul id="total-field" class="tabled-data inverse-bold ">
                        <li>
                           <label>Итого к оплате</label>
                           <div class="value text-right">{total_value}</div>
                        </li>
                     </ul>

                  </div>
               </div>
            </div>

            <div class="place-order-button">
               <button type="submit" class="le-button huge" id="checkout-submit">Оформить заказ</button>
            </div>

            {terms-personal}

            <div id="checkout-response"></div>

         </form>
      </div>
   </div>
</section>
