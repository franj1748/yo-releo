const sub_uri = location.origin+'/'+location.pathname.split('/')[1];

    // Guardar en localstorage la url de la página del checkout.
    jQuery.ajax({
		url : ajax_const.ajaxurl,
		type: 'post',
		data: {
			action: 'modper_ajax_checkout'
		},
		success: function(response){
		    if(!localStorage.getItem('url_checkout')){
		        localStorage.setItem('url_checkout',  response);
		    }
		},
		error: function(response){
            console.log('Ha ocurrido un error, intentalo de nuevo.');	
        }
	});


if (sub_uri == `${location.origin}/shop`){
    if(document.querySelector('a.woocommerce-LoopProduct-link.woocommerce-loop-product__link')){
        const sub_contenedorenlaces = Array.from(document.querySelectorAll('a.woocommerce-LoopProduct-link.woocommerce-loop-product__link'));
        const relacionados = sub_contenedorenlaces.length == 10 ? 4 : sub_contenedorenlaces.length == 9 ? 3 : sub_contenedorenlaces.length == 8 ? 2 : sub_contenedorenlaces.length == 7 ? 1 : sub_contenedorenlaces.length == 3 ? 0 : 0;
        const sub_productos = sub_contenedorenlaces.filter((producto, indice) => indice < (sub_contenedorenlaces.length - relacionados) / 2);
        const sub_productos_mobile = sub_contenedorenlaces.filter((producto, indice) => indice >= (sub_contenedorenlaces.length - relacionados) / 2 && indice < sub_contenedorenlaces.length - relacionados);
        
        // Sub modal de descripción en desktop con petición ajax para procesar la descripción del producto al que se le ha hecho clic.
        sub_productos.forEach(producto => {
            
            // Reducir el tamaño de la imagen del prodcuto. 
            const thumbnail = producto.firstElementChild.tagName == 'SPAN' ? producto.children[1] : producto.firstElementChild;
            thumbnail.classList.add('img-fluid', 'mx-auto', 'd-block');
            thumbnail.style.width = '100%';
            thumbnail.style.height = '300px';
            
            producto.addEventListener('click', e => {
                e.preventDefault();
                const sub_popUp = document.querySelector('#sub_modalComplementarios');
                const sub_popUp_contenedor_body = document.querySelector('#column_contenedor');
                const sub_product_url = (e.target.tagName == 'A') ? e.target.href : e.target.parentElement.href;
                sub_popUp.classList.add('show');
                sub_popUp.style.display = 'block';
                const sub_product_title = (e.target.tagName == 'A') ? e.target.children[2].textContent : e.target.parentElement.children[2].textContent;
                const sub_popUp_title = document.querySelector('#sub_modalComplementariosLabel');
                sub_popUp_title.textContent = `Más sobre ${sub_product_title}`;
                if(localStorage.getItem(sub_product_url)){
                    sub_popUp_contenedor_body.innerHTML = localStorage.getItem(sub_product_url);
                }else{
                    jQuery.ajax({
            			url : ajax_const.ajaxurl,
            			type: 'post',
            			data: {
            				action: 'modper_ajax_readmore',
            				url_product: sub_product_url,
            				nonce: ajax_const.nonce
            			},
            			beforeSend: function(){
            				sub_popUp_contenedor_body.innerHTML = `<img src="${location.origin}/wp-content/plugins/modificaciones-personalizadas/includes/assets/img/loadingto.gif" class="img-fluid mx-auto d-block" style="height: 40px;" alt="loading">`;
            			},
            			success: function(response){
            			    localStorage.setItem(sub_product_url,  response);
            				sub_popUp_contenedor_body.innerHTML = response;
            			},
            			error: function(response){
                            sub_popUp_contenedor_body.innerHTML = `Ha ocurrido un error, intentalo de nuevo.`;	
                        }
    		        });
                }
                const sub_cerrar = document.querySelector('#sub_cerrar');
                sub_cerrar.addEventListener('click', e => {
                    e.preventDefault();
                    sub_popUp.style.display = 'none';
                });
            });
        });
        
        // Sub modal de descripción en mobile con petición ajax para procesar la descripción del producto al que se le ha hecho clic.
        sub_productos_mobile.forEach(producto => {
            
            const thumbnail = producto.firstElementChild.tagName == 'SPAN' ? producto.children[1] : producto.firstElementChild;
            thumbnail.classList.add('img-fluid', 'mx-auto', 'd-block');
            thumbnail.style.width = '100%';
            thumbnail.style.height = '150px';
            
            producto.addEventListener('click', e => {
                e.preventDefault();
                const sub_popUp_mobile = document.querySelector('#sub_modalComplementarios_mobile');
                const sub_popUp_contenedor_body_mobile = document.querySelector('#column_contenedor_mobile');
                const sub_product_url_mobile = (e.target.tagName == 'A') ? e.target.href : e.target.parentElement.href;
                sub_popUp_mobile.classList.add('show');
                sub_popUp_mobile.style.display = 'block';
                const sub_product_title_mobile = (e.target.tagName == 'A') ? e.target.children[1].textContent : e.target.parentElement.children[1].textContent;
                const sub_popUp_title_mobile = document.querySelector('#sub_modalComplementariosLabel_mobile');
                sub_popUp_title_mobile.textContent = `Más sobre ${sub_product_title_mobile}`;
                if(localStorage.getItem(sub_product_url_mobile)){
                    sub_popUp_contenedor_body_mobile.innerHTML = localStorage.getItem(sub_product_url_mobile);
                }else{
                    jQuery.ajax({
            			url : ajax_const.ajaxurl,
            			type: 'post',
            			data: {
            				action : 'modper_ajax_readmore',
            				url_product: sub_product_url_mobile,
            				nonce: ajax_const.nonce
            			},
            			beforeSend: function(){
            				sub_popUp_contenedor_body_mobile.innerHTML = `<img src="${location.origin}/wp-content/plugins/modificaciones-personalizadas/includes/assets/img/loadingto.gif" class="img-fluid mx-auto d-block" style="height: 40px;" alt="loading">`;
            			},
            			success: function(response){
            			    localStorage.setItem(sub_product_url_mobile,  response);
            				sub_popUp_contenedor_body_mobile.innerHTML = response;		
            			},
            			error: function(response){
                            sub_popUp_contenedor_body_mobile.innerHTML = `Ha ocurrido un error, intentalo de nuevo.`;	
                        }
        		    });
                }
                const sub_cerrar_mobile = document.querySelector('#sub_cerrar_mobile');
                sub_cerrar_mobile.addEventListener('click', e => {
                    e.preventDefault();
                    sub_popUp_mobile.style.display = 'none';
                });
            });
        });
    }
}










