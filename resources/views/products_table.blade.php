<style>
  .table th {
    background-color: #007bff;
    color: white;
    white-space: nowrap;
  }

  td {
    vertical-align: middle;
    word-break: break-word;
    max-width: 150px;
  }

  .wrap-text {
    white-space: normal;
  }
</style>

<table class="table table-bordered table-hover text-center align-middle">
  <thead>
    <tr>
      <th>ID</th>
      <th>Product Name</th>
      <th>Price</th>
      <th>Qty</th>
      <th>Image</th>
      <th class="wrap-text">Created At</th>
      <th class="wrap-text">Updated At</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($products as $row)
      <tr>
        <td>{{ $row->id }}</td>
        <td>{{ $row->productname }}</td>
        <td>${{ $row->price }}</td>
        <td>{{ $row->quantity }}</td>
        <td>
          @if($row->image)
            <img src="{{ asset($row->image) }}" width="50">
          @endif
        </td>
        <td class="wrap-text">{{ $row->create_at }}</td>
        <td class="wrap-text">{{ $row->update_at }}</td>
        <td>
          <button class="btn btn-sm btn-warning"
            onclick="fillForm('{{ $row->id }}','{{ $row->productname }}','{{ $row->price }}','{{ $row->quantity }}')">
            Update
          </button>

          <form action="/product-action" method="POST" style="display:inline">
            @csrf
            <input type="hidden" name="id" value="{{ $row->id }}">
            <button type="submit" name="action" value="Delete" class="btn btn-sm btn-danger">
              Delete
            </button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
