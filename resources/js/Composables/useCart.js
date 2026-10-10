import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

export function useCart() {
    const page = usePage()

    const addToCart = (product, quantity = 1) => {
        router.post(route('cart.store', product.id), { quantity }, {
            preserveScroll: true,

            onSuccess: () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Added to cart',
                    text: page.props.flash?.success ?? 'Product added to cart successfully.',
                    timer: 2000,
                    showConfirmButton: false,
                })
            },

            onError: (errors) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: Object.values(errors)[0] ?? 'Could not add the product.',
                })
            },
        })
    }

    return { addToCart }
}
