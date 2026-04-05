@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <h1>Sliders</h1>

    <form id="slider-form">
        <input type="hidden" name="id" id="slider-id" />
        <div>
            <label>Page slug</label>
            <input type="text" name="page_slug" id="page_slug" value="home" />
        </div>
        <div>
            <label>Section key</label>
            <input type="text" name="section_key" id="section_key" value="slider" />
        </div>
        <div>
            <label>Title</label>
            <input type="text" name="title" id="title" />
        </div>
        <div>
            <label>Subtitle</label>
            <input type="text" name="subtitle" id="subtitle" />
        </div>
        <div>
            <label>Description (HTML allowed)</label>
            <textarea name="html_content" id="html_content" rows="4"></textarea>
        </div>
        <div>
            <label>Link</label>
            <input type="text" name="link" id="link" />
        </div>
        <div>
            <label>Image</label>
            <input type="file" name="image" id="image" accept="image/*" />
        </div>
        <div>
            <button id="save-btn">Save</button>
            <button id="reset-btn" type="button">Reset</button>
        </div>
    </form>

    <hr />
    <div id="list"></div>
</div>

<script>
async function fetchSliders(){
    const res = await fetch('/sliders', { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    const data = await res.json();
    const rows = Array.isArray(data) ? data : (data.data || []);
    const list = document.getElementById('list');
    list.innerHTML = '';
    rows.forEach(s => {
        const el = document.createElement('div');
        el.innerHTML = `<h3>${s.title || ''}</h3><div>${s.subtitle||''}</div><div>${s.html_content||s.description||''}</div><div>${s.link||''}</div><div>${s.image?'<img src="/storage/'+s.image+'" style="max-width:200px"/>':''}</div><button data-id="${s.id}" class="edit">Edit</button> <button data-id="${s.id}" class="del">Delete</button>`;
        list.appendChild(el);
    });
    document.querySelectorAll('.edit').forEach(b=>b.addEventListener('click', e=>editItem(e.target.dataset.id)));
    document.querySelectorAll('.del').forEach(b=>b.addEventListener('click', e=>deleteItem(e.target.dataset.id)));
}

async function editItem(id){
    const res = await fetch('/sliders/'+id, { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    const raw = await res.json();
    const s = raw.data || raw;
    document.getElementById('slider-id').value = s.id;
    document.getElementById('page_slug').value = s.page_slug;
    document.getElementById('section_key').value = s.section_key;
    document.getElementById('title').value = s.title || '';
    document.getElementById('subtitle').value = s.subtitle || '';
    document.getElementById('html_content').value = s.html_content || s.description || '';
    document.getElementById('link').value = s.link || '';
}

async function deleteItem(id){
    if(!confirm('Delete?')) return;
    await fetch('/sliders/'+id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    fetchSliders();
}

document.getElementById('slider-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const id = document.getElementById('slider-id').value;
    const fd = new FormData();
    fd.append('page_slug', document.getElementById('page_slug').value);
    fd.append('section_key', document.getElementById('section_key').value);
    fd.append('title', document.getElementById('title').value);
    fd.append('subtitle', document.getElementById('subtitle').value);
    fd.append('html_content', document.getElementById('html_content').value);
    fd.append('link', document.getElementById('link').value);
    const file = document.getElementById('image').files[0];
    if(file) fd.append('image', file);

    if(id){
        fd.append('_method','PUT');
        await fetch('/sliders/'+id, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    } else {
        await fetch('/sliders', { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
    }
    document.getElementById('slider-form').reset();
    fetchSliders();
});

document.getElementById('reset-btn').addEventListener('click', ()=>{ document.getElementById('slider-form').reset(); document.getElementById('slider-id').value = ''; });

fetchSliders();
</script>

@endsection
