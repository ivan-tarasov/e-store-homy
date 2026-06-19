<section class="container admin-page" style="padding:2em 0;">
   <div class="row">
      <div class="col-12 col-md-3">{nav}</div>
      <div class="col-12 col-md-9">
         <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="mb-0">Товары ({count})</h1>
            <a class="le-button" href="/admin/products/new">+ Новый товар</a>
         </div>
         <table class="table">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>Название</th>
                  <th>Категория</th>
                  <th>Бренд</th>
                  <th class="text-end">Цена</th>
                  <th class="text-center">Запас</th>
                  <th>Наличие</th>
                  <th class="text-end">Рейтинг</th>
                  <th></th>
               </tr>
            </thead>
            <tbody>{rows}</tbody>
         </table>
      </div>
   </div>
</section>
