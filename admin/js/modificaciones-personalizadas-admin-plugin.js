// Botón de envío
const submit = document.querySelector('#submit_modial_relay');

// Campos de formulario
const order            = document.querySelector('#order');
const seller           = document.querySelector('#seller');
const buyer            = document.querySelector('#buyer');
const etiquette_uri    = document.querySelector('#etiquette_uri');


submit.addEventListener('click', e => {

    e.preventDefault();

    jQuery.ajax({
		url : ajax_mondial.ajaxurl_mondial,
		type: 'post',
		data: {
			action: 'modper_ajax_mondial_relay',
			order: order.value,
			seller: seller.value,
			buyer: buyer.value,
			etiquette_uri: etiquette_uri.value,
			nonce: ajax_mondial.nonce
		},
		beforeSend: function(){
		    
		    const loading = document.createElement('img');
		    loading.id = 'loading';
            loading.src = 'https://yoreleo.es/wp-content/plugins/modificaciones-personalizadas/includes/assets/img/loadingto.gif';
            loading.classList.add('img-fluid', 'mx-auto', 'd-block');
            loading.style.height = '20px';
            loading.alt = 'loading';
            
        	submit.parentElement.appendChild(loading);
		},
		success: function(response){
		    
		    if( response == 4210){
		        
		        submit.parentElement.children.loading.remove();
    		    const error = document.createElement('span');
                error.id = 'error';
                error.textContent = 'No existe un número de expedición para la orden dada!';
                submit.parentElement.appendChild(error);
                
                const intert = setTimeout(() => {
                    submit.parentElement.children.error.remove();
                    clearTimeout(intert);
                }, 2500);
                
		    }else{
		        
    		    submit.parentElement.children.loading.remove();
    		    const success = document.createElement('span');
                success.id = 'success';
                success.textContent = 'Enviado!';
                submit.parentElement.appendChild(success);
                
                order.value = '';
                seller.value = '';
                buyer.value = '';
                etiquette_uri.value = '';
                
                const inter = setTimeout(() => {
                    submit.parentElement.children.success.remove();
                    clearTimeout(inter);
                }, 2000); 
		    }
		},
		error: function(response){
		    
		    submit.parentElement.children.loading.remove();
		    const error = document.createElement('span');
            error.id = 'error';
            error.textContent = 'Ha ocurrido un error, intentalo de nuevo!';
            submit.parentElement.appendChild(error);
            
            const intert = setTimeout(() => {
                submit.parentElement.children.error.remove();
                clearTimeout(intert);
            }, 2000);	
	    }
    });

});





