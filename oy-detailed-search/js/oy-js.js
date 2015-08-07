jQuery(document).ready(function($) {

	$("#txtFromDate").datepicker({
        numberOfMonths: 1,
        dateFormat : 'yy-mm-dd',
        onSelect: function(selected) {
          $("#txtToDate").datepicker("option","minDate", selected)
        }
    });
    $("#txtToDate").datepicker({ 
        numberOfMonths: 1,
        dateFormat : 'yy-mm-dd',
        onSelect: function(selected) {
           $("#txtFromDate").datepicker("option","maxDate", selected)
        }
    });  
    
$.datepicker.regional['tr'] = {
		closeText: 'kapat',
		prevText: '&#x3C;geri',
		nextText: 'ileri&#x3e',
		currentText: 'bugün',
		monthNames: ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran',
		'Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],
		monthNamesShort: ['Oca','Şub','Mar','Nis','May','Haz',
		'Tem','Ağu','Eyl','Eki','Kas','Ara'],
		dayNames: ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'],
		dayNamesShort: ['Pz','Pt','Sa','Ã‡a','Pe','Cu','Ct'],
		dayNamesMin: ['Pz','Pt','Sa','Ça','Pe','Cu','Ct'],
		weekHeader: 'Hf',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['tr']);
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
	  if($(this).val()=="posts")
		  $('#oy-etiket-just-for-yazi').fadeIn(300);
	  else
		  $('#oy-etiket-just-for-yazi').fadeOut(300);
  });
  $("#oy-arama-tur-js-icin").change();
});

function validateSearch_real() {

    var author_slug             = document.getElementById('input').author_slug.value;
    var date_begin              = document.getElementById('input').date_begin.value;
    var date_end                = document.getElementById('input').date_end.value;
    var words_included          = document.getElementById('input').words_included.value;
    var words_ordered           = document.getElementById('input').words_ordered.value;
    var words_at_least_one      = document.getElementById('input').words_at_least_one.value;
    var words_excluded          = document.getElementById('input').words_excluded.value;
    var likes                   = document.getElementById('input').likes.value;
    var inc_tags                = document.getElementById('input').inc_tags.value;
    var inc_tags_all            = document.getElementById('input').inc_tags_all.value;

    if(author_slug === "" && (date_begin === "" || date_end === "") && words_included === "" && words_ordered === "" && words_at_least_one === "" && words_excluded === "" && inc_tags === "" && likes === '0' && inc_tags_all === ""){
        alert("En az bir parametre girmelisin!");
        return false;
    }
}
