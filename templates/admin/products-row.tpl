<tr>
   <td>{id}</td>
   <td><a href="{url}" target="_blank">{name}</a></td>
   <td>{category}</td>
   <td>{brand}</td>
   <td class="text-end">{price}</td>
   <td class="text-center">{stock}</td>
   <td><span class="stock-pill stock-{stock_class}">{stock_label}</span></td>
   <td class="text-end">{rating} ★</td>
   <td>
      <a class="btn btn-sm btn-outline-secondary" href="/admin/products/{id}/edit">Изм.</a>
      <form method="post" action="/admin/products/{id}/delete" style="display:inline;" onsubmit="return confirm('Удалить товар?');">
         <button class="btn btn-sm btn-outline-danger" type="submit">Удал.</button>
      </form>
   </td>
</tr>
