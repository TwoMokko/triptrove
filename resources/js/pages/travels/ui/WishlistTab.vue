<script setup lang="ts">
    import { ref } from "vue"
    import { mdiPencil, mdiDelete } from '@mdi/js'
    import InputCustom from "@/shared/ui/InputCustom.vue"
    import ButtonCustom from "@/shared/ui/ButtonCustom.vue"
    import Icon from "@/shared/ui/Icon.vue"

    interface WishListItem {
        id: number,
        title: string,
        checked: boolean
    }

    const wishlist = ref<WishListItem[]>([])
    const newWish = ref<string>('')

    const addWish = (): void => {
        const lastId = wishlist.value.length > 0
            ? Math.max(...wishlist.value.map(itm => itm.id))
            : 0

        const newWishItem: WishListItem = {
            id: lastId + 1,
            title: newWish.value,
            checked: false
        }

        wishlist.value.push(newWishItem)

        newWish.value = ''
    }
    const changeCheckedWishlist = (event: Event, id: number): void => {
        const target = event.target as HTMLInputElement

        wishlist.value = wishlist.value.map(itm => {
            if (itm.id === id) return { ...itm, checked: target.checked }
            return itm
        })
    }
    const removeItemInWishlist = (id: number): void => {
        wishlist.value = wishlist.value.filter(itm => itm.id !== id)
    }

    // TODO: добавить возможность отсюда перенести в планирование или в случившееся путешествие
</script>

<template>
    <section>
        <article class="w-1/2">
            <label
                v-for="wishItem in wishlist"
                :key="wishItem.id"
                class="flex gap-4 p-4"
            >
                <span v-if="wishItem.checked">+</span>
                <input
                    type="checkbox"
                    :checked="wishItem.checked"
                    @change="changeCheckedWishlist($event, wishItem.id)"
                >
                <span>{{ wishItem.title }}</span>
                <button
                    @click="removeItemInWishlist(wishItem.id)"
                    class="cursor-pointer hover:text-dark text-secondary ml-auto"
                >
                    <Icon
                        :iconPath="mdiDelete"
                        class="w-6 h-6"
                    />
                </button>
            </label>
        </article>

        <div class="flex gap-2 w-1/2">
            <InputCustom v-model:value="newWish" :placeholder="'новое место'" :type="'text'" />
            <ButtonCustom @click="addWish" :text="'Добавить'" />
        </div>
    </section>
</template>
