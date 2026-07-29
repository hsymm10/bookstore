import Swal from 'sweetalert2'

export default defineNuxtPlugin(() => {
  const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (t) => {
      t.addEventListener('mouseenter', Swal.stopTimer)
      t.addEventListener('mouseleave', Swal.resumeTimer)
    },
  })

  return {
    provide: {
      swal: Swal,
      toast,
    },
  }
})
