$(document).ready(function() {


    var dropZone = $('#dropZone'),
        dropZoneText = $('#dropZone > #text'),
        dropZonePlace = $('#dropZone > .sortable'),
        maxFileSize = 64*1024*1024; // максимальный размер файла - 64 мб.

	dropZone.on("click","a.delete_image",function(){
		
		id = $(this).attr("data-id");
		
		console.log("удаляем "+ id);
		
		var xhr = new XMLHttpRequest();
		var formData = new FormData();
		formData.append('id', id);
		xhr.onreadystatechange = stateChange;
		xhr.open('POST', "/web/app_dev.php/admin/image_delete/" + id);
		xhr.send(formData);
		
		function stateChange(event) {
			if (event.target.readyState == 4) {
				if (event.target.status == 200) {
					dropZoneText.text("Картинка удалена");
					$("#image-"+id).remove();
				} else {
					dropZoneText.text('Произошла ошибка!');
					dropZone.addClass('error');
				}
			}
		}
		return false;
	})

		
	if (typeof(window.FileReader) == 'undefined') {
		dropZone.text('Не поддерживается браузером!');
		dropZone.addClass('error');
	}
	
	
	dropZone[0].ondragover = function() {
		dropZone.addClass('hover');
		return false;
	};
		
	dropZone[0].ondragleave = function() {
		dropZone.removeClass('hover');
		return false;
	};
	
	
	dropZone[0].ondrop = function(event) {
		event.preventDefault();
		dropZone.removeClass('hover');
		dropZone.addClass('drop');
	
		var formData = new FormData();
		for (var i = 0, file; file = event.dataTransfer.files[i]; ++i) {
			formData.append('files['+i+']', file);
		  }	
		if(i == 0 ){return false;}
		var xhr = new XMLHttpRequest();
		xhr.upload.addEventListener('progress', uploadProgress, false);
		xhr.onreadystatechange = stateChange;
		xhr.open('POST', "/web/app_dev.php/admin/image_upload");
		xhr.send(formData);
	};
	
	function uploadProgress(event) {
		var percent = parseInt(event.loaded / event.total * 100);
		dropZoneText.text('Загрузка: ' + percent + '%');
	}

	function stateChange(event) {
		if (event.target.readyState == 4) {
			if (event.target.status == 200) {
				dropZoneText.text("");
				dropZonePlace.append(event.target.responseText);
			} else {
				dropZone.text('Произошла ошибка!');
				dropZone.addClass('error');
			}
		}
	}	
	
	function dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }
    } else {
        out = obj;
    }
    console.log(out);
	}
	

});
