<section class="container admin-page" style="padding:2em 0;">
   <div class="row">
      <div class="col-12 col-md-3">{nav}</div>
      <div class="col-12 col-md-9">
         <p><a href="/admin/orders/">← Все заказы</a></p>
         <h1>Заказ {id}</h1>

         <div class="admin-meta-grid">
            <div><label>Создан</label><div>{created}</div></div>
            <div><label>Статус</label><div>{status}</div></div>
            <div><label>Клиент</label><div>{customer}</div></div>
            <div><label>Телефон</label><div>{phone}</div></div>
            <div><label>Адрес</label><div>{address}</div></div>
            <div><label>Аккаунт</label><div>{user_line}</div></div>
         </div>

         {note}

         <h2 style="margin-top:2em;">Позиции</h2>
         <table class="table">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>Товар</th>
                  <th class="text-end">Цена</th>
                  <th class="text-center">Кол-во</th>
                  <th class="text-end">Сумма</th>
               </tr>
            </thead>
            <tbody>{rows}</tbody>
            <tfoot>
               <tr>
                  <th colspan="4" class="text-end">Итого</th>
                  <th class="text-end">{total}</th>
               </tr>
            </tfoot>
         </table>
      </div>
   </div>
</section>
