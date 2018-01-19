import fontawesome from '@fortawesome/fontawesome'
import faFreeSolid from '@fortawesome/fontawesome-free-solid'
import faFreeRegular from '@fortawesome/fontawesome-free-regular'
import faFreeBrands from '@fortawesome/fontawesome-free-brands'
import faProSolid from '@fortawesome/fontawesome-pro-solid'
import faProRegular from '@fortawesome/fontawesome-pro-regular'
import faProLight from '@fortawesome/fontawesome-pro-light'

module.exports = function(){

  fontawesome.library.add(faFreeSolid, faFreeRegular, faFreeBrands, faProSolid, faProRegular, faProLight)

}
