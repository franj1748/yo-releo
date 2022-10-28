// Crear un elemento que permita saber si es la primera vez que un usuario visita la página. 
if (localStorage.getItem('visita') == null){
   localStorage.setItem('visita', 1);
}

// Mostrar botón de añadir al carrito sólo en los productos de la ventana modal. 
const uri = location.origin+'/'+location.pathname.split('/')[1];
if (uri == `${location.origin}/shop`){
    
    const productsLink  = Array.from(document.querySelectorAll('a.woocommerce-LoopProduct-link.woocommerce-loop-product__link'));
    const btnAtoC = productsLink.map(productLink => productLink.nextElementSibling);
    const relacionados = btnAtoC.length == 10 ? 4 : btnAtoC.length == 9 ? 3 : btnAtoC.length == 8 ? 2 : btnAtoC.length == 7 ? 1 : btnAtoC.length == 3 ? 0 : 0;
    const btnAtoCModals = btnAtoC.filter((btn, indice) => indice < (btnAtoC.length - relacionados) / 2);
    const btnAtoCModalsMobile = btnAtoC.filter((btn, indice) => indice >= (btnAtoC.length - relacionados) / 2 && indice < btnAtoC.length - relacionados);
    btnAtoCModals.forEach(btnAtoCModal => {
        btnAtoCModal.style.display = 'block';
        btnAtoCModal.classList.add('product_type_simple', 'add_to_cart_button', 'ajax_add_to_cart', 'elementor-button', 'elementor-size-md', 'text-white');
        btnAtoCModal.style.backgroundColor = '#722620';
    });
    btnAtoCModalsMobile.forEach(btnAtoCModalMobile => {
        btnAtoCModalMobile.style.display = 'block';
        btnAtoCModalMobile.classList.add('product_type_simple', 'add_to_cart_button', 'ajax_add_to_cart', 'elementor-button', 'elementor-size-md', 'text-white');
        btnAtoCModalMobile.style.backgroundColor = '#722620';
    });
}
//-->


// Ocultar opción de transferencia del monedero en la página de mi saldo.
if (uri == `${location.origin}/mi-saldo`){
    
    const tabsTransferenciaSaldo  = document.querySelector('.tabs').firstElementChild;
    tabsTransferenciaSaldo.style.display = 'none';
    
}
//-->

// Aumentar el tamaño de la fuente en los inputs y textareas de la página para evitar el zoom automático al escribir. 
if (document.querySelector('input')){

    const inputs = Array.from(document.querySelectorAll('input'));
    inputs.forEach(input => {
        input.style.fontSize = '17px';
    });
}

if (document.querySelector('textarea')){
    
    const textareas = Array.from(document.querySelectorAll('textarea'));
    textareas.forEach(textarea => {
        textarea.style.fontSize = '17px';
    });

    // const textarea = document.querySelector('#um_message_text');
    // textarea.style.fontSize = '17px';
    //document.querySelector('[name="content"]');
    
}
//-->

// Enviar nombre de usuario en url para la impresión de los libros en la pestaña de actividad. 
const tabLibros = location.search;
const nameTab  = MODPER_const.tab;

if (tabLibros == `?profiletab=${nameTab}`){

    const user = document.querySelector('.um-name').childNodes[1].textContent ? document.querySelector('.um-name').childNodes[1].textContent : '';
    window.location.href = window.location.href + "&user=" + user;
    
}
//-->

// Eliminar los espacios vacios entre los libros de la pestaña de actividad. 
for (let i=1; i < 50; i++){
    
    if(document.querySelector(`.woocommerce.columns-${i}`)){
        document.querySelector(`.woocommerce.columns-${i}`).style.whiteSpace = 'nowrap';
    }
    
}
//-->

// Ocultar el botón de comprar, precio y guardar de los productos que se muestran en la pestaña de actividad de cada usuario. 
if(uri == `${location.origin}/user`){
    
    const precios = Array.from(document.querySelectorAll('span.price'));
    const btns = Array.from(document.querySelectorAll('.add_to_cart_button'));
    const imgProducts = Array.from(document.querySelectorAll('.attachment-woocommerce_thumbnail'));
    
    precios.forEach(precio => {
        precio.style.display = 'none';
    });

    btns.forEach(btn => {
        btn.style.display = 'none';
    });
    
    imgProducts.forEach(img => {
        img.style.borderRadius = '5px';
    });
    
    // Botón de mensajería en perfil de seguidor
    if(document.querySelector('.um-message-btn.um-button')){
        const mensaje_followers = document.querySelector('.um-message-btn.um-button');
        mensaje_followers.style.background = '#722620';
    }
    
}
//-->

// Eliminar la etiqueta meta de viewport existente para que se agregue una nueva mediante PHP. 
document.querySelector('meta[name="viewport"]').remove();

/*
// Funciones antiguas
// Eliminar el cuadro de agregar post en la pestaña social activity
if(document.querySelector('.um-activity-publish')){
    document.querySelector('.um-activity-publish').style.display = 'none';
}

// Cambiar el texto del campo de agregar imagen
if(document.querySelector('#featured_image')){
    document.querySelector('#featured_image :nth-child(2)').firstChild.textContent = 'Clic para añadir imagen';
}

const origen = location.origin;
const path = location.pathname;
const array = path.split('/');
const uri = origen + '/' + array[1];

// Cambiar el texto de la pestaña de actividad
if(uri == 'https://www.leogratis.com/user'){
    document.querySelector('.um-profile-nav-activity > a:nth-child(2) > span').textContent = 'Libros';
}

// Rellenar y ocultar los campos del formulario del front que el usuario desee. 
const slugId  = MODPER_const.slugs.split(',');
const valores = MODPER_const.valores.split(',');
let i = 0;
slugId.forEach(slug => {

    if(document.querySelector(`#${slug}`)){
        document.querySelector(`#${slug}`).style.display = 'none';
        document.querySelector(`#${slug}`).value = `${valores[i]}`;
        i++;
    }else{
        i++;
    }

}); 
*/
