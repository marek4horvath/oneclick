<template>
    <NuxtLayout>
        <div class="invate-company">
            <v-form ref="form" v-model="valid" >
                <v-container>
                    <v-row>
                        <v-col cols="6" class="py-0">
                            <v-text-field 
                                v-model="invateCompanyEmail"
                                :label="$t('companies.email')" 
                                type="text" 
                                required
                                :rules="emailRules"
                            ></v-text-field>
                        </v-col>

                        <v-col cols="12">
                            <v-btn color="primary" text @click="sendEmail">{{ $t('companies.invateCompany') }}</v-btn>
                        </v-col>
                    </v-row>
                </v-container>

                <!-- Snackbar for notifications -->
                <v-snackbar v-model="snackbar.visible" :color="snackbar.color">
                    {{ snackbar.message }}
                    <template v-slot:actions>
                        <v-btn text @click="snackbar.visible = false">Close</v-btn>
                    </template>
                </v-snackbar>
            </v-form>
        </div>
    </NuxtLayout>
</template>

<script setup lang="ts">
import emailjs from "@emailjs/browser";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const form = ref(null);
const valid = ref(false);
const invateCompanyEmail = ref('');
const emailRules = [
    (v: string) => !!v || 'E-mail is required',
    (v: string) => /.+@.+/.test(v) || 'E-mail must be valid',
];

const SERVICE_ID = import.meta.env.VITE_APP_API_SERVICE_ID;
const TEMPLATE_ID = import.meta.env.VITE_APP_API_TEMPLATE_ID;
const USER_ID = import.meta.env.VITE_APP_API_USER_ID;

const snackbar = ref({
    visible: false,
    message: '',
    color: ''
});

definePageMeta({
    title: 'Invite company',
    name: 'invate-company',
    layout: 'dashboard',
    middleware: 'auth'
});

const sendEmail = async() => {
    try {
        const response = await emailjs.send(
            SERVICE_ID,   // Service ID
            TEMPLATE_ID,  // Template ID
            {
                from_name: "Inobat",
                from_email: 'inobat@test.com',
                to_email: invateCompanyEmail.value,
                message: t('invateCompanyMessage')
            },
            USER_ID //User ID or Public Key
        );

        snackbar.value = {
            visible: true,
            message: t('emailSuccessfully'),
            color: 'success'
        };
    } catch (error) {
        snackbar.value = {
            visible: true,
            message: t('emailFailed'),
            color: 'error'
        };
        console.error("Error:", error);
    }
};
</script>

<style lang="scss" scoped>
.invate-company {
    width: 50%;
    margin-right: auto;
    margin-left: 50px;

}
</style>
