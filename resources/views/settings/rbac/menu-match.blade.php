<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - Menu Match</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('settings.rbac.partials.styles')
    @include('settings.rbac.partials.d365-rbac-page-styles')
</head>
<body
    data-url-mappings="{{ route('settings.menu-match.api.mappings.index') }}"
    data-url-mappings-save="{{ route('settings.menu-match.api.mappings.update') }}"
    data-url-available-menus="{{ route('settings.menu-match.api.available-menus.index') }}"
    data-url-assign="{{ route('settings.menu-match.api.assign.store') }}"
>
@include('partials.global-company-selector')
@include('settings.rbac.partials.sidebar')
<main class="main">
    <h1 class="page-title">Menu match</h1>
    <p class="rbac-page-intro">Map menu keys to required permissions.</p>
    <div id="flashError" class="flash-error"></div>
    <div class="d365-cmd-bar"><button id="cmdSave" class="d365-cmd d365-cmd-primary">Save mapping</button></div>
    <div class="table-card"><table class="users-grid"><thead><tr><th>Menu</th><th>Key</th><th>Permission</th></tr></thead><tbody id="matchTableBody"></tbody></table></div>
</main>
<script>
(function(){const b=document.body,u=b.dataset.urlMappings,s=b.dataset.urlMappingsSave,c=document.querySelector('meta[name="csrf-token"]').content,t=document.getElementById('matchTableBody');let rows=[];function api(p,o={}){const h=Object.assign({'Accept':'application/json','X-CSRF-TOKEN':c,'X-Requested-With':'XMLHttpRequest'},o.headers||{});if(o.body)h['Content-Type']='application/json';return fetch(p,Object.assign({},o,{headers:h})).then(r=>r.json().then(d=>({r,d})).catch(()=>({r,data:{}})));}function load(){api(u).then(x=>{const d=x.d||{};rows=d.menu_items||[];const perms=d.permissions||[];t.innerHTML='';rows.forEach(r=>{const tr=document.createElement('tr');const opts=['<option value=\"\">(none)</option>'].concat(perms.map(p=>'<option value=\"'+p.id+'\" '+((r.permission_id==p.id)?'selected':'')+'>'+p.slug+'</option>'));tr.innerHTML='<td>'+r.menu_label+'</td><td>'+r.menu_key+'</td><td><select data-id=\"'+r.id+'\">'+opts.join('')+'</select></td>';t.appendChild(tr);});});}document.getElementById('cmdSave').addEventListener('click',()=>{const maps=[...t.querySelectorAll('select')].map(sel=>({id:Number(sel.dataset.id),permission_id:sel.value?Number(sel.value):null}));api(s,{method:'PUT',body:JSON.stringify({mappings:maps})}).then(()=>load());});load();})();
</script>
</body>
</html>

