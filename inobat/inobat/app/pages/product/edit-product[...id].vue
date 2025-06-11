<template>
    <NuxtLayout>
        <div class="edit-product">
            <v-form ref="form" v-model="valid" @keyup.native.enter="submit">
                <v-container>
                    <v-row no-gutters>
                        <v-col>
                            <v-text-field 
                                v-model="formProduct.name" 
                                :label="$t('products.name')" 
                                type="text" 
                                required
                                :rules="nameRules"
                            ></v-text-field>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col>
                            <v-textarea v-model="formProduct.description" :label="$t('products.description')"></v-textarea>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col>
                            <ImageUpload 
                                :imageProp="formProduct.productImage" 
                                @update:image="updateImage" 
                            />
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col>
                            <v-btn color="primary" text @click="submit">{{ $t('products.addProduct') }}</v-btn>
                        </v-col>
                    </v-row>
                </v-container>
            </v-form>
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">

import type { ProductPayload } from '~/interface/product';

const productStore = useProductStore()
const router = useRouter()
const route = useRoute()
const form = ref(null)
const formProduct:ProductPayload = ref({
    id: '',
    name: '',
    description: '',
    productImage: ''
})
const valid = ref(false)
const nameRules = [
    (v: string) => !!v || 'Name is required',
    (v: string) => v.trim().length > 0 || 'Name cannot be empty',
];

definePageMeta({
    title: 'Edit product',
    name: 'edit-product[...id]',
    layout: 'dashboard',
    middleware: 'auth'
});

const url = route.path
const productId = url.split('/').pop()

const submit = () => {
    const formValidation: any = form.value
    formValidation.validate()

    if (!valid.value) {
        return
    }

    productStore.editProduct(productId, formProduct.value)
    router.push('/products')
}

const updateImage = (newImage) => {
    formProduct.value.productImage = newImage;
};

onMounted(() => {
    const product = productStore.getProductById(productId)
    formProduct.value = product  
})

</script>

<style lang="scss" scoped>
.edit-product {
    width: 50%;
    margin-right: auto;
    margin-left: 50px;
}
</style>
