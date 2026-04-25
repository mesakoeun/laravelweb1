<!DOCTYPE html>
<html>
<head>
<title>Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
<button class="btn btn-primary mb-3" onclick="openModal()">Add</button>
<div id="data"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="m">
<div class="modal-dialog">
<form id="f" enctype="multipart/form-data">
@csrf
<input type="hidden" name="id" id="id">

<div class="modal-content p-3">
<input name="productname" id="name" placeholder="Name" class="form-control mb-2">
<input name="price" id="price" placeholder="Price" class="form-control mb-2">
<input name="quantity" id="qty" placeholder="Qty" class="form-control mb-2">
<input type="file" name="image" class="form-control mb-2">

<button class="btn btn-success" onclick="save('Insert')">Save</button>
<button class="btn btn-warning d-none" id="updateBtn" onclick="save('Update')">Update</button>
</div>

</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const modal = new bootstrap.Modal(document.getElementById('m'))

function load(){
 fetch('/products').then(r=>r.text()).then(d=>document.getElementById('data').innerHTML=d)
}

function openModal(){
 document.getElementById('f').reset()
 document.getElementById('updateBtn').classList.add('d-none')
 modal.show()
}

function edit(p){
 document.getElementById('id').value=p.id
 document.getElementById('name').value=p.productname
 document.getElementById('price').value=p.price
 document.getElementById('qty').value=p.quantity
 document.getElementById('updateBtn').classList.remove('d-none')
 modal.show()
}

function save(action){
 event.preventDefault()
 let form=new FormData(document.getElementById('f'))
 form.append('action',action)

 fetch('/save',{method:'POST',body:form})
 .then(()=>{modal.hide();load();})
}

function del(id){
 if(confirm('Delete?')){
  fetch('/delete',{
   method:'POST',
   headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
   body:JSON.stringify({id})
  }).then(()=>load())
 }
}

load()
</script>

</body>
</html>
