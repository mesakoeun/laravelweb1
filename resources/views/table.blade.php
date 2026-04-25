<table class="table table-bordered text-center">
<thead>
<tr>
<th>ID</th><th>Name</th><th>Price</th><th>Qty</th><th>Image</th><th>Action</th>
</tr>
</thead>
<tbody>
@foreach($products as $p)
<tr>
<td>{{ $p->id }}</td>
<td>{{ $p->productname }}</td>
<td>${{ $p->price }}</td>
<td>{{ $p->quantity }}</td>
<td>@if($p->image)<img src="{{ asset($p->image) }}" width="50">@endif</td>
<td>
<button onclick="edit({{ json_encode($p) }})" class="btn btn-warning btn-sm">Edit</button>
<button onclick="del({{ $p->id }})" class="btn btn-danger btn-sm">Delete</button>
</td>
</tr>
@endforeach
</tbody>
</table>
