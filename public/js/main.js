function $(id) {
    return document.getElementById(id);
}

function toggledisplay(id) {
    var el = $(id);
    var st = el.style.display;
    st = st === 'none' ? 'block' : 'none';
    el.style.display = st;
    return false;
}
