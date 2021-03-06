<template>
    <div>
        <v-toolbar
            class="elevation-10">

            <v-banner>После того как вы внесли все изменения, пожалуйста сохраните их</v-banner>
            <v-spacer></v-spacer>

            <v-btn color="primary" @click="saveArticle" :loading="btnLoading">
                Сохранить
            </v-btn>

            <template v-slot:extension>

                <v-tabs
                    v-model="tab"
                    centered
                >
                    <v-tab
                        v-for="n in tabs"
                        :key="n.name"
                    >
                        {{ n.title }}
                        <v-icon>{{ n.icon }}</v-icon>
                    </v-tab>
                </v-tabs>
            </template>
        </v-toolbar>

        <v-tabs-items v-model="tab" class="mt-5">
            <v-form ref="form"
                    v-model="validForm"
                    lazy-validation>
                <v-tab-item>
                    <v-card flat max-width="700px" max-height="2000px" class="ml-auto mr-auto mt-5 mb-10">
                        <v-row>
                            <v-text-field
                                v-model="article.title"
                                label="Название"
                                :rules="notEmptyRule"
                                counter="255"
                                clearable
                            ></v-text-field>
                        </v-row>
                        <v-row>
                            <v-text-field
                                v-model="article.slug"
                                label="Слаг"
                                :rules="notEmptyRule"
                                counter="255"
                                clearable
                            ></v-text-field>
                        </v-row>
                        <v-row>
                            <v-text-field
                                v-model="article.description"
                                counter="255"
                                :rules="notEmptyRule"
                                label="Краткое описание"
                                clearable
                            ></v-text-field>
                        </v-row>
                        <v-row>
                            <v-select v-model="article.rubric_id"
                                      :items="rubrics"
                                      item-text="title"
                                      item-value="id"
                                      label="Рубрика"
                                      required
                            >
                            </v-select>
                        </v-row>
                        <v-row>
                            <v-text-field
                                v-model="article.read_time"
                                label="Время прочтения"
                                clearable
                            ></v-text-field>
                        </v-row>
                        <v-row>
                            <v-col>
                                Опубликовать
                                <v-simple-checkbox
                                    v-model="article.posted"
                                    label="Опубликовать"
                                ></v-simple-checkbox>
                            </v-col>
                            <v-col>
                                Long-Read
                                <v-simple-checkbox
                                    v-model="article.is_long_read"
                                    label="Long read"
                                ></v-simple-checkbox>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-combobox
                                v-model="article.tags"
                                :items="existingTags"
                                chips
                                clearable
                                label="Теги"
                                multiple
                                prepend-icon="mdi-filter-variant"
                                solo
                            >
                                <template v-slot:selection="{ attrs, item, select, selected }">
                                    <v-chip
                                        v-bind="attrs"
                                        :input-value="selected"
                                        close
                                        @click="select"
                                        @click:close="removeTag(item)"
                                    >
                                        <strong>#{{ item }}</strong>&nbsp;
                                    </v-chip>
                                </template>
                            </v-combobox>
                        </v-row>
                    </v-card>
                </v-tab-item>
                <v-tab-item>
                    <v-card flat max-width="900px" height="2000px" class="ml-auto mr-auto mt-5">
                        <v-row>
                            <v-select v-model="article.author"
                                      :items="authors"
                                      item-text="full_name"
                                      item-value="id"
                                      label="Автор статьи"
                                      required
                            >
                            </v-select>
                        </v-row>
                        <v-banner class="mt-5 mb-2">Ко-авторы
                            <v-btn small class="ml-10" @click="addStaff">Добавить еще</v-btn>
                        </v-banner>
                        <v-simple-table dense>
                            <template v-slot:default>
                                <thead>
                                <tr>
                                    <th class="text-left">
                                        Должность в создании статьи
                                    </th>
                                    <th class="text-left">
                                        Имя
                                    </th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr
                                    v-for="(st,index) in article.staff"
                                    :key="index + 'st'"
                                >
                                    <td>
                                        <v-text-field single-line :rules="notEmptyRule" v-model="st.title"
                                                      placeholder="Впишите позицию"></v-text-field>
                                    </td>
                                    <td>
                                        <v-text-field single-line v-model="st.full_name" :rules="notEmptyRule"
                                                      placeholder="Впишите имя"></v-text-field>
                                    </td>
                                    <td>
                                        <v-btn color="red" x-small depressed @click="removeStaff(index)">
                                            X
                                        </v-btn>
                                    </td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>
                    </v-card>
                </v-tab-item>
                <v-tab-item>
                    <crop-image class="mb-10"
                                v-show="!article.is_long_read"
                                ref="cropperBig"
                                :value="article.preview_image_big"
                                key="big"
                                :min-crop-box-height="400"
                                :min-crop-box-width="1200"
                                :dialog-max-width="1300"
                                :aspect-ratio="3"
                                label="Выберите картинку с большим размером"
                                :rules="notEmptyRule"
                    />

                    <crop-image ref="cropperSmall"
                                :value="article.preview_image_small"
                                key="small"
                                label="Выберите картинку с меньшим размером"
                                :dialog-max-width="700"
                                :min-crop-box-height="300"
                                :min-crop-box-width="600"
                                :aspect-ratio="2"
                                :rules="notEmptyRule"/>

                    <v-text-field
                        class="ml-10 mr-10"
                        v-model="article.photography"
                        label="Автор фотографии"
                        clearable
                    ></v-text-field>
                </v-tab-item>
                <v-tab-item>
                    <v-card>
                        <v-btn id="insert-table">Добавить таблицу</v-btn>
                        <editor ref="editor" :content="article.content"></editor>
                    </v-card>
                </v-tab-item>
            </v-form>
        </v-tabs-items>
    </div>
</template>
<script>
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import CropImage from "../Reusable/CropImage";
import Editor from "../Reusable/Editor";

export default {
    name: "CreateArticle",
    components: {
        VueCropper,
        CropImage,
        Editor,
    },
    mounted() {
        this.$store.commit('changeHeaderText', 'Создать статью')
        this.getAuthors()
        this.getRubrics()
        this.getTags()
    },
    data() {
        return {
            validForm: false,
            existingTags: [],
            imgSrc: '',
            cropImg: '',
            data: null,
            authors: [],
            article: {
                title: '',
                slug: '',
                rubric_id: '',
                description: '',
                author: '',
                staff: [{}],
                read_time: '',
                read_time_value: '',
                photography: '',
                posted: false,
                preview_image: '',
                is_long_read: false,
                tags: []
            },
            rubrics: [],
            tab: 3,
            tabs: [
                {
                    title: 'Основная информация',
                    name: 'general-info',
                    icon: 'mdi-collage'
                },
                {
                    title: 'Авторы',
                    name: 'authors',
                    icon: 'mdi-human-edit',
                },
                {
                    title: 'Обложка статьи',
                    name: 'upload-image',
                    icon: 'mdi-image-plus'
                },
                {
                    title: 'Контент',
                    name: 'content',
                    icon: 'mdi-content-copy'
                }
            ],
            notEmptyRule: [
                v => !!v || 'Обязательное поле',
            ],
            btnLoading: false
        }
    },
    methods: {
        getAuthors() {
            this.$http.get('/api/authors')
                .then(res => {
                    console.log(res)
                    this.authors = res.data.data.data;
                })
        },
        getRubrics() {
            this.$http.get('/api/rubrics')
                .then(res => {
                    this.rubrics = res.data.data.data;
                })
        },
        removeStaff(index) {
            this.article.staff.splice(index, 1)
        },
        addStaff() {
            this.article.staff.push({title: 'Художник', full_name: 'Тестов Тест'})
        },
        saveArticle() {
            this.btnLoading = true;
            if (!this.$refs.form.validate()) {
                this.$store.commit('triggerSnack', {
                    text: 'Перепроверьте данные, некоторые поля пустые',
                    color: 'orange'
                })
                return;
            }

            var formData = new FormData();
            this.buildFormData(formData, this.article)

            if (this.$refs.cropperBig && this.$refs.cropperBig.croppedBlob) {
                formData.append('preview_image_big', this.$refs.cropperBig.croppedBlob)
            }
            if (this.$refs.cropperSmall && this.$refs.cropperSmall.croppedBlob) {
                formData.append('preview_image_small', this.$refs.cropperSmall.croppedBlob)
            }

            if (this.$refs.editor && CKEDITOR.instances.editor111.getData()) {
                formData.append('content', CKEDITOR.instances.editor111.getData())
            } else {
                this.$store.commit('triggerSnack', {
                    text: 'Заполните контент, хотя бы одним символом',
                    color: 'orange'
                })
                return;
            }

            this.$http.post('/api/articles/create', formData)
                .then(res => {
                    this.$store.commit('triggerSnack', {text: 'Статья создана', color: 'green'})
                    this.btnLoading = false;
                    this.$router.push({name: 'update-article', params: {id: res.data.data.id}});
                })
                .catch(err => {
                    if (err.response.status === 422) {
                        this.$store.commit('triggerSnack', {text: 'Проверьте целостность данных', color: 'red'})
                    } else {
                        this.$store.commit('triggerSnack', {text: 'Ошибка, перезагрузите страницу', color: 'red'})
                    }
                    this.btnLoading = false;
                })
        },
        buildFormData(formData, data, parentKey) {
            if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File)) {
                Object.keys(data).forEach(key => {
                    this.buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key);
                });
            } else {
                const value = data == null ? '' : data;

                formData.append(parentKey, value);
            }
        },
        getTags() {
            this.$http('/api/tags')
                .then(res => {
                    this.existingTags = res.data.data.map(a => a.name);
                })
                .catch(err => {

                })
        },
        removeTag(item) {
            this.article.tags.splice(this.article.tags.indexOf(item), 1)
            this.article.tags = [...this.article.tags]
        },
    }
}
</script>

<style scoped>

</style>
