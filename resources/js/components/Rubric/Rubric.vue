<template>
    <div>

        <v-dialog
            v-model="dialog"
            max-width="700px"
        >
            <template v-slot:activator="{ on, attrs }">
                <v-btn
                    color="primary"
                    dark
                    class="mt-5 mb-3 ml-3"
                    @click="changeRubricType('create')"
                    v-bind="attrs"
                    v-on="on"
                >
                    Добавить рубрику
                </v-btn>
            </template>
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{
                            dialogType === 'create' ? 'Добавить рубрику' : ' Редактировать рубрику'
                        }}</span>
                </v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="dialogRubric.title"
                                    label="Название"
                                    :rules="[value => !!value || 'Обязательное поле']"
                                    :error-messages="titleError"
                                ></v-text-field>
                            </v-col>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="dialogRubric.slug"
                                    label="Slug"
                                    :rules="[value => !!value || 'Обязательное поле']"
                                    :error-messages="slugError"
                                ></v-text-field>
                            </v-col>
                            <v-col
                                cols="12"
                            >
                                <v-text-field
                                    v-model="dialogRubric.description"
                                    label="Описание"
                                    :counter="254"
                                ></v-text-field>
                            </v-col>
                            <v-col
                                cols="3"
                            >
                                <v-text-field
                                    v-model="dialogRubric.order"
                                    label="Позиция"
                                    :rules="[value => !!value || 'Обязательное поле']"
                                    type="number"
                                    :error-messages="orderError"
                                ></v-text-field>
                            </v-col>
                            <v-col
                                cols="3"
                            >
                                <v-checkbox
                                    v-model="dialogRubric.is_preferable"
                                    label="Основной"
                                ></v-checkbox>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue darken-1"
                        text
                        @click="closeDialog"
                    >
                        Отмена
                    </v-btn>
                    <v-btn
                        color="blue darken-1"
                        text
                        @click="submitDialog"
                    >
                        Сохранить
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <ApiDataTable
            :headers="headers"
            loading-text="Загрузка рубрик"
            api-url="/api/rubrics"
            ref="rubrics"
            :api-includes="apiIncludes"
        >
        </ApiDataTable>

        <v-row justify="space-between" class="mt-1 ml-2 mr-2">
            <v-btn
                depressed
                color="danger"
                @click="deleteRubrics"
            >
                Удалить отмеченные
            </v-btn>
            <v-btn
                depressed
                color="success"
                @click="saveInfo"
            >
                Сохранить
            </v-btn>
        </v-row>
    </div>
</template>

<script>
import ApiDataTable from "../Reusable/ApiDataTable";

export default {
    name: "Rubric",
    components: {ApiDataTable},
    created() {
        this.$store.commit('changeHeaderText', 'Управление рубриками');
    },
    data() {
        return {
            slots: [
                {field: 'is_visible', type: 'checkbox'},
                {field: 'is_preferable', type: 'checkbox'},
                {field: 'order', type: 'edit-text-field'},
            ],
            orderError: null,
            titleError: null,
            slugError: null,
            descriptionError: null,
            dialogType: '',
            dialogRubric: {is_preferable: true, is_visible: true},
            dialog: false,
            headers: [
                {
                    text: 'ID',
                    value: 'id',
                    type: 'text'
                },
                {
                    text: 'Название',
                    value: 'title',
                    type: 'edit-text-field'
                },
                {
                    text: 'Слаг',
                    value: 'slug',
                    type: 'edit-text-field'
                },
                {
                    text: 'Описание',
                    value: 'description',
                    type: 'edit-text-field',
                    sortable: false,
                },
                {
                  text: 'Количество статей',
                  value: 'articles_count',
                  type: 'text'
                },
                {
                    text: 'Позиция',
                    value: 'order',
                    type: 'edit-number-field'
                },
                {
                    text: 'Основной',
                    value: 'is_preferable',
                    sortable: false,
                    type: 'checkbox'
                }
            ],
            rubrics: null,
            apiIncludes: 'articlesCount'
        }
    },
    methods: {
        saveInfo() {
            if (!this.isValidOrders()) {
                this.$store.commit('triggerSnack', {text: 'Позиции рубик не должны повторятся!', color: 'red'})
                return;
            }

            this.$refs.rubrics.loading = true;
            this.$http.post('/api/rubrics/edit-info', {
                rubrics: this.$refs.rubrics.items
            })
                .then(res => {
                    this.$store.commit('triggerSnack', {text: 'Успех', color: 'green'})
                    this.$refs.rubrics.getDataFromApi()
                })
                .catch(err => {
                    this.$store.commit('triggerSnack', {text: 'Ошибка', color: 'red'})
                })
        },
        isValidOrders() {
            let result = this.$refs.rubrics.items.map(a => a.order);
            return new Set(result).size === this.$refs.rubrics.items.length
        },
        closeDialog() {
            this.dialog = false;
            this.dialogRubric = {};
            this.dialogType = '';
            this.orderError = null;
            this.titleError = null;
            this.slugError = null;
            this.descriptionError = null;
        },
        submitDialog() {
            this.titleError = null;
            this.orderError = null;
            this.$refs.rubrics.loading = true;
            this.$http.post('/api/rubrics/upsert', {
                rubric: this.dialogRubric
            }).then(res => {
                this.$store.commit('triggerSnack', {text: 'Успех', color: 'green'})
                this.closeDialog();
                this.$refs.rubrics.getDataFromApi();
            })
                .catch(err => {
                    if ('rubric.order' in err.response.data.errors) {
                        this.orderError = 'Должен быть уникальным';
                    }

                    if ('rubric.title' in err.response.data.errors) {
                        this.titleError = 'Должен быть уникальным';
                    }

                    if ('rubric.slug' in err.response.data.errors) {
                        this.slugError = 'Должен быть уникальным';
                    }

                    this.$store.commit('triggerSnack', {text: err.message, color: 'red'})
                    this.$refs.rubrics.loading = false;
                })
        },
        changeRubricType(type) {
            this.dialogType = type;
            this.dialogRubric = {};
        },
        deleteRubrics() {
            if (this.$refs.rubrics.selected.length === 0) return;
            this.$refs.rubrics.loading = true;
            this.$http.delete('/api/rubrics',{
                data: {
                    rubrics: this.$refs.rubrics.selected
                }
            })
            .then(res => {
                this.$refs.rubrics.loading = false;
                this.$store.commit('triggerSnack', {text:'Успешно удалено', color: 'success'})

                this.$refs.rubrics.getDataFromApi();
            })
        }
    }
}
</script>

<style scoped>

</style>
