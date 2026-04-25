<!DOCTYPE html>
<html>
<head>
<title>Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .table th {
        background-color: #007bff; /* Blue header */
        color: white;
        white-space: nowrap;
      }
  .img-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.8);
    justify-content: center;
    align-items: center;
  }
  .img-modal.active { display: flex; }
  .img-modal-content {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #modalImg {
    max-width: 80vw;
    max-height: 80vh;
    transition: transform 0.2s;
    pointer-events: none;
    display: block;
  }
  .img-modal-close {
    position: absolute;
    top: -35px; right: 0;
    font-size: 2rem;
    color: white;
    cursor: pointer;
    background: none;
    border: none;
  }
  .img-modal-zoom {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10000;
    display: flex;
    gap: 10px;
    background: rgba(0,0,0,0.6);
    padding: 6px 18px;
    border-radius: 8px;
  }
  .img-modal-zoom button {
    font-size: 1.5rem;
    padding: 4px 14px;
    cursor: pointer;
    border: none;
    border-radius: 4px;
    background: white;
  }
  .thumb-img {
    width: 50px;
    cursor: pointer;
  }
</style>
</head>
<body class="p-4">
<div class="container">
  <button class="btn btn-primary mb-3" onclick="openModal()">Add</button>
  <div id="data"></div>
</div>

<!-- Product Form Modal -->
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

<!-- ✅ Image Preview Modal -->
<div id="imgModal" class="img-modal">
  <div class="img-modal-content">
    <button class="img-modal-close" onclick="closeImgModal()">&times;</button>
    <img id="modalImg" src="" alt="Preview">
  </div>
  <div class="img-modal-zoom">
    <button onclick="zoomImg(-0.2)">−</button>
    <button onclick="zoomImg(0.2)">+</button>
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

// ✅ Image modal — must be here in main file, NOT in the partial
var imgZoom = 1;
function openImgModal(imgEl){
  document.getElementById('modalImg').src = imgEl.getAttribute('data-src');
  document.getElementById('modalImg').style.transform = 'scale(1)';
  document.getElementById('imgModal').classList.add('active');
  imgZoom = 1;
}
function closeImgModal(){
  document.getElementById('imgModal').classList.remove('active');
  document.getElementById('modalImg').src = '';
  imgZoom = 1;
}
function zoomImg(delta){
  imgZoom = Math.min(Math.max(imgZoom + delta, 0.2), 5);
  document.getElementById('modalImg').style.transform = 'scale(' + imgZoom + ')';
}
document.getElementById('imgModal').addEventListener('click', function(e){
  if(e.target === this) closeImgModal();
});

load()
</script>
</body>
</html>
