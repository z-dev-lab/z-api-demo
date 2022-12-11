function getQueryParams(url) {
     const paramArr = url.slice(url.indexOf('?') + 1).split('&');
     const params = {};
     paramArr.map(param => {
          const [key, val] = param.split('=');
          if (key) params[key] = decodeURIComponent(val);
     })
     return params;
}

function currencyValue(currency) {
    return Number(currency.replace(/[^0-9.-]+/g,""));
}

function changeSelection(element1, element2, name, value) {
    if (element2.style.color=="lightgrey") {
       element2.style.color="black";
       element2.style.borderColor="black";
       document.getElementById(name).value='on';
       element1.style.color="lightgrey";
       element1.style.borderColor="lightgrey";
    } else {
       element2.style.color="lightgrey";
       element2.style.borderColor="lightgrey";
       document.getElementById(name).value='';
       element1.style.color="black";
       element1.style.borderColor="black";
    }
}

Number.prototype.round = function(places) {
      return +(Math.round(this + "e+" + places)  + "e-" + places);
}

function populateForm(form) {
    params = getQueryParams(document.location.search);
    for ( var i = 0; i < form.elements.length; i++ )  {
        if (params[form.elements[i].name])
           form.elements[i].value=params[form.elements[i].name];
    }
}

function getFormURI_old(form) {
    params = getQueryParams(document.location.search);
    uri="?";
    for ( var i = 0; i < form.elements.length; i++ )  {
        if (params[form.elements[i].name])
            uri = uri + form.elements[i].name + "=" + encodeURIComponent(params[form.elements[i].name]) + "&";
    }
    return uri;
}

function getFormURI(form) {
    uri="?";
    for ( var i = 0; i < form.elements.length; i++ )  {
        if (form.elements[i].value)
            uri = uri + form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value) + "&";
    }
    return uri;
}

