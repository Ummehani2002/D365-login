<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Permissions</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body data-url-permissions="{{ route('settings.permissions.api.permissions.index') }}" data-url-permissions-store="{{ route('settings.permissions.api.permissions.store') }}">
@include('partials.global-company-selector')
@include('settings.rbac.partials.sidebar')
<main class="main">
    <h1 class="page-title">Permissions</h1>
    <p class="rbac-page-intro">Manage permissions used by roles.</p>
    <div id="flashError" class="flash-error"></div>
    <div class="d365-cmd-bar"><button id="cmdNew" class="d365-cmd d365-cmd-primary">+ New</button></div>
    <div class="table-card"><table class="users-grid"><thead><tr><th>Slug</th><th>Name</th></tr></thead><tbody id="permsTableBody"></tbody></table></div>
</main>
<script>
(function(){const b=document.body,u=b.dataset.urlPermissions,s=b.dataset.urlPermissionsStore,c=document.querySelector('meta[name="csrf-token"]').content,t=document.getElementById('permsTableBody');function api(p,o={}){const h=Object.assign({'Accept':'application/json','X-CSRF-TOKEN':c,'X-Requested-With':'XMLHttpRequest'},o.headers||{});if(o.body)h['Content-Type']='application/json';return fetch(p,Object.assign({},o,{headers:h})).then(r=>r.json().then(d=>({r,d})).catch(()=>({r,data:{}})));}function load(){api(u).then(x=>{const d=x.d||x.data||{};t.innerHTML='';(d.permissions||[]).forEach(p=>{const tr=document.createElement('tr');tr.innerHTML='<td>'+p.slug+'</td><td>'+p.name+'</td>';t.appendChild(tr);});});}document.getElementById('cmdNew').addEventListener('click',()=>{const slug=prompt('Permission slug');if(!slug)return;const name=prompt('Permission name');if(!name)return;api(s,{method:'POST',body:JSON.stringify({slug,name})}).then(()=>load());});load();})();
</script>
</body>
</html>

