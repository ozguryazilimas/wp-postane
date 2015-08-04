jQuery(document).ready(function($) {
$("#oy-arama-container").css("width",$("#oy-unique").width());
$("#oy-hide-tip").click(function(){
	$("#oy-hide-button").click();
});
$("#oy-hide-button").click(function(){
	$t=$(this);
	if($t.hasClass("oy-rotate"))
	{
		$t.removeClass("oy-rotate");
		$t.addClass("oy-de-rotate");
		$('#oy-arama-container').animate({height:'toggle'},350);
	}
	else
	{
		$t.removeClass("oy-de-rotate");
		$t.addClass("oy-rotate");
		$('#oy-arama-container').animate({height:'toggle'},350);
	}

});
$("#oy-arama-tur-js-icin").change(function(){
	if($(this).val()=="arama_yazi")
		$('#oy-etiket-just-for-yazi').fadeIn(300);
	else
		$('#oy-etiket-just-for-yazi').fadeOut(300);
});
$("#oy-arama-tur-js-icin").change();
});

function validateSearch_real() {

    var yazar_isim   = document.getElementById('input').yazar_isim.value;
    var tarih_ilk  = document.getElementById('input').tarih_ilk.value;
    var tarih_son = document.getElementById('input').tarih_son.value;
    var kelime_gecen   = document.getElementById('input').kelime_gecen.value;
    var kelime_daginik   = document.getElementById('input').kelime_daginik.value;
    var kelime_sirali   = document.getElementById('input').kelime_sirali.value;
    var kelime_gecmeyen   = document.getElementById('input').kelime_gecmeyen.value;
    var tutma   = document.getElementById('input').tutma.value;
	var etiketler=document.getElementById('input').etiketler.value;
	var etiketler_hepsi=document.getElementById('input').etiketler_hepsi.value;

    if(etiketler_hepsi==="" && yazar_isim ==="" && tarih_ilk ==="" && tarih_son ==="" && kelime_gecen ==="" && kelime_daginik ==="" && kelime_sirali ==="" && kelime_gecmeyen ==="" && tutma ==='0' && etiketler===""){
        alert("En az bir parametre girmelisin!");
        return false;
    }
}
