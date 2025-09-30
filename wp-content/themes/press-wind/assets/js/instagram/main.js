function instagramOverride(){
  setTimeout(() => {
    const instagramSection = document.querySelector('.instagram-gallery-list');
    if(instagramSection){
      const template = `<a class="instagram-gallery-item instagram-gallery-item--cols-3 ">
        <h2 class="text-orange text-[32px] flex items-end justify-center text-right flex-col">
          Suivez-l'actualité 
          <sub class="font-arial font-[400] text-[32px]">de Campings17</sub>
        </h2>
      </a>`;
      instagramSection.insertAdjacentHTML('afterbegin', template);
    } else {
    }
  }, 2000); // 2000 ms = 2 sec
}

export default instagramOverride;
