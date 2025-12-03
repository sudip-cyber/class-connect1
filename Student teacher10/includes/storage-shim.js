// Storage shim: syncs client localStorage with server-side kv_store on load
(function(){
  const API_BASE = 'api';
  const originalSet = Storage.prototype.setItem.bind(localStorage);
  const originalGet = Storage.prototype.getItem.bind(localStorage);
  const originalRemove = Storage.prototype.removeItem.bind(localStorage);

  // On load, fetch dump and populate localStorage (server wins)
  function populateFromServer(){
    try{
      fetch(API_BASE + '/kv_dump.php', {cache: 'no-store'})
        .then(r => r.json())
        .then(json => {
          if(json && json.ok && json.data){
            Object.keys(json.data).forEach(k => {
              try{ originalSet(k, json.data[k]); }catch(e){}
            });
          }
        }).catch(()=>{});
    }catch(e){}
  }

  // send set to server (async)
  function remoteSet(key, value){
    try{
      fetch(API_BASE + '/kv_set.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({key: key, value: value})
      }).catch(()=>{});
    }catch(e){}
  }

  // send remove to server
  function remoteRemove(key){
    try{
      fetch(API_BASE + '/kv_remove.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({key: key})
      }).catch(()=>{});
    }catch(e){}
  }

  // override setItem/removeItem so pages continue to use localStorage API
  Storage.prototype.setItem = function(key, value){
    try{ originalSet(key, value); }catch(e){}
    // async persist
    remoteSet(key, value);
  };

  Storage.prototype.removeItem = function(key){
    try{ originalRemove(key); }catch(e){}
    remoteRemove(key);
  };

  // populate immediately
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', populateFromServer);
  else populateFromServer();

})();
