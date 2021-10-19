
function saveandclose(wwwroot, certid) {
    $('#mod-pdcertificate-builder').attr('contenteditable', false);

    var layout;

    var elements = ['seal', 'signature', 'borders', 'watermark', 'title', 'grade', 'outcome', 'user', 'authority',' custometext', 'statement', 'code'];
    for (elm in elements) {
        layout[elm] = getlayout('#pdcertificate-'+elm);
    }

    serialized = JSON.stringify(layout);

    url = wwwroot+'/mod/pdcertificate/ajax/services.php';
    params = new Object();
    params.what = 'savelayout';
    params.id = certid;
    params.layout = serialized;
    
    $().post(url, params);

    window.close();
}

function getlayout(id) {
    layoutobj = new Object();
    layoutobj.x = parseInt($(id).css('left').replace('px', '')) - parseInt($('#mod-pdcertificate-builder').css('left').replace('px', ''));
    layoutobj.x = parseInt($(id).css('top').replace('px', '')) - parseInt($('#mod-pdcertificate-builder').css('top').replace('px', ''));
    layoutobj.w = $(id).css('width');
    layoutobj.h = $(id).css('height');
    layoutobj.fs = $(id).css('font-size');
    layoutobj.c = $(id).css('color');

    return layoutobj;
}

function fontsizeplus(divid) {
    objkey = '#'+divid+ '-text';
    fontsize = $(objkey).css('font-size');
    fontsizevalue = parseInt(fontsize.replace(/[a-z]+/, ''));
    fontsizeunit = fontsize.replace(/^\d+/, '');
    fontsizevalue++;
    newsize = fontsizevalue+fontsizeunit;
    $(objkey).css('font-size', newsize);
}

function fontsizeminus(divid) {
    objkey = '#'+divid+ '-text';
    fontsize = $(objkey).css('font-size');
    fontsizevalue = parseInt(fontsize.replace(/[a-z]+/, ''));
    fontsizeunit = fontsize.replace(/^\d+/, '');
    if (fontsizevalue > 1) {
        fontsizevalue--;
    }
    newsize = fontsizevalue+fontsizeunit;
    $(objkey).css('font-size', newsize);
}

var originx;
var originy;
var captured = false;

function moveitem(event) {
    itemname = event.data.objid;
    movex = event.pageX - originx;
    movey = event.pageY - originy;
    toppos = parseInt($('#'+itemname).css('top').replace('px', ''));
    leftpos = parseInt($('#'+itemname).css('left').replace('px', ''));
    $('#'+itemname).css('top', (toppos + movey)+'px');
    $('#'+itemname).css('left', (leftpos + movex)+'px');
    originx = leftpos + movex;
    originy = toppos + movey;
}

function capture(event, owner) {
    objkey = owner.parentNode.id.replace('-move', '');
    if (!captured) {
        captured = true;
        $(document).bind('mousemove', {'objid': objkey}, moveitem);
        originx = event.pageX;
        originy = event.pageY;
        $(document).bind('onclick', {'objid': objkey}, capture);
    } else {
        $(document).unbind('mousemove');
        captured = false;
    }
}

function release(event, owner) {
    objkey = owner.parentNode.id.replace('-move', '');
    $('#'+objkey).unbind('mousedrag');
}
