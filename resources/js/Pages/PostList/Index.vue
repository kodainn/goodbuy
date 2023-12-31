<script setup>
import CommonHeader from "@/Components/CommonHeader.vue";
import SvgIcon from "@jamescoyle/vue-icon";
import { mdiHeart } from "@mdi/js";
import { mdiPlus } from "@mdi/js";
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import Button from "@/Components/Button.vue";
import PullDown from "@/Components/PullDown.vue";
import axios from "axios";
import TextField from "@/Components/TextField.vue";
import ErrorMessage from "@/Components/ErrorMessage.vue";
import TextArea from "@/Components/TextArea.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    loginUser: Object,
    typeDivKv: Object,
    posts: Object,
    errors: Object,
    currentPage: Number,
    maxPage: Number,
    genre: {
        type: Number,
        default: 0
    }
});

const dialog = ref(false);
const frontPost = ref(props.posts);
const frontGenre = ref(props.genre);
const frontCurrentPage = ref(props.currentPage);
const frontmaxPage = ref(props.maxPage);
const searchGenre = ref("");
const innerWidth = ref(window.innerWidth);

const heart = mdiHeart;
const plus = mdiPlus;


//投稿削除
const postDelete = async(post_uuid) => {
    if(!confirm('本当に削除しますか?')) {return;}
    await axios.delete('/postlist/' + post_uuid);
    await axios.get('/postlist/getpost/' + frontCurrentPage.value + '/search/' + frontGenre.value)
    .then(res => {
        frontPost.value = res.data['posts'];
        frontCurrentPage.value = res.data['currentPage'];
        frontmaxPage.value = res.data['maxPage'];
    });
}


const form = reactive({
    imageList: [],
    title: null,
    review: null,
    genreDiv: null
});

//投稿作成
const sendForm = () => {
    router.post('/postlist', form, {
        onSuccess: async() => {
            imageReviewList.value = [];
            form.imageList = [];
            form.title = null;
            form.review = null;
            form.genreDiv = null;
            dialog.value = false;
            await axios.get('/postlist/getpost/' + frontCurrentPage.value + '/search/' + frontGenre.value)
            .then(res => {
                 frontPost.value = res.data['posts'];
                 frontCurrentPage.value = res.data['currentPage'];
                 frontmaxPage.value = res.data['maxPage'];
            });
        }
    });
}

const imageReviewList = ref([]);
const pushImagePathList = ($event) => {
    if ($event.target.files.length > 0) {
        const file = $event.target.files[0];
        form.imageList.push([file]);
        imageReviewList.value.push([URL.createObjectURL(file)]);
    }
}
</script>

<template>
    <v-app>
        <v-container>
            <CommonHeader :loginUser="loginUser"></CommonHeader>
            <v-main>
                <v-container>
                    <!-- 検索ボックスエリア -->
                    <v-row>
                        <v-col
                            offset="0"
                            cols="12"
                            sm="6"
                            md="4"
                            lg="3"
                        >
                            <PullDown
                                :typeDivKv="typeDivKv"
                                name="ジャンル"
                                v-model="searchGenre"
                            >
                            </PullDown>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="6"
                            md="4"
                            lg="3"
                        >
                            <Link
                                v-if="searchGenre ? true : false"
                                :href="route('postlist.search.paginate', {page: 1, genre: searchGenre})">
                                <Button
                                    v-if="innerWidth >= 600"
                                    name="検索"
                                    color="black"
                                    :style="{ height: '67%' }"
                                >
                                </Button>
                                <Button
                                    v-else
                                    name="検索"
                                    color="black"
                                    width="100%"
                                >
                                </Button>
                            </Link>
                        </v-col>
                    </v-row>
                    <!-- 一覧表示エリア -->
                    <v-row>
                        <v-col
                            offset="0"
                            cols="12"
                            sm="6"
                            md="4"
                            lg="3"
                            v-for="post of frontPost"
                        >
                            <v-card class="mx-auto my-12" max-width="374">
                                <template v-slot:loader="{ isActive }">
                                    <v-progress-linear
                                        :active="isActive"
                                        color="deep-purple"
                                        height="4"
                                        indeterminate
                                    ></v-progress-linear>
                                </template>
                                <v-img
                                    cover
                                    height="250"
                                    :src="post['images'][0]['post_image_path']"
                                ></v-img>
                                <v-card-text>
                                    <div class="text-h5 text-primary">
                                        {{ typeDivKv[post['genre_div']] }}
                                    </div>
                                    <div class="text-h6">
                                        {{ post['title'].length >= 15 ? post['title'].substr(0, 15) + '...' : post['title'] }}
                                    </div>
                                </v-card-text>
                                <v-card-actions>
                                    <v-row>
                                        <v-col>
                                            <Link
                                                :href="
                                                    route('postlist.show', { post_uuid: post['post_uuid'] })
                                                "
                                            >
                                                <Button
                                                    variant="text"
                                                    name="詳細を見る"
                                                    color="blue"
                                                >
                                                </Button>
                                            </Link>
                                            <Button
                                                v-if="loginUser && loginUser['user_uuid'] === post['user_uuid']"
                                                variant="text"
                                                name="削除"
                                                color="red"
                                                @click="postDelete(post['post_uuid'])"
                                            >
                                            </Button>
                                        </v-col>
                                        <v-col offset="3">
                                            <template v-if="loginUser">
                                                <svg-icon
                                                    type="mdi"
                                                    :path="heart"
                                                    style="color: red"
                                                    @click="subGoodStack(post['post_uuid'])"
                                                ></svg-icon
                                                >{{ post['goods_count'] }}
                                            </template>
                                        </v-col>
                                    </v-row>
                                </v-card-actions>
                            </v-card>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col
                            cols="12"
                            sm="12"
                            md="12"
                            lg="9"
                        >
                            <Pagination
                                :currentPage="frontCurrentPage"
                                :maxPage="frontmaxPage"
                                :genre="frontGenre"
                            >
                            </Pagination>
                        </v-col>
                    </v-row>
                    <!-- 投稿作成エリア -->
                    <template v-if="loginUser">
                        <v-row justify="center">
                            <v-dialog
                                v-model="dialog"
                                transition="dialog-bottom-transition"
                                width="70%"
                            >
                                <template v-slot:activator="{ props }">
                                    <v-btn
                                        class="ma-2 fixed-button"
                                        icon="mdi-cloud-upload"
                                        color="#993300"
                                        v-bind:="props"
                                    >
                                        <svg-icon type="mdi" :path="plus"></svg-icon>
                                    </v-btn>
                                </template>
                                <v-card>
                                    <v-card-title>
                                        <span class="text-h5">新規投稿</span>
                                    </v-card-title>
                                    <form @submit.prevent="sendForm" enctype="multipart/form-data">
                                        <v-card-text>
                                            <v-container>
                                                <v-row>
                                                    <template v-for="index in imageReviewList.length">
                                                        <v-col
                                                            v-if="(index - 1) === 0 || (index - 1) % 4 === 0"
                                                            offset-md="2"
                                                            offset-lg="2"
                                                            cols="4"
                                                            sm="2"
                                                            md="2"
                                                            lg="2"
                                                        >
                                                            <v-img
                                                                height="150"
                                                                width="150"
                                                                :src="imageReviewList[index - 1][0]"
                                                            ></v-img>
                                                        </v-col>
                                                        <v-col
                                                            v-else
                                                            cols="4"
                                                            sm="2"
                                                            md="2"
                                                            lg="2"
                                                        >
                                                            <v-img
                                                                height="150"
                                                                width="150"
                                                                :src="imageReviewList[index - 1][0]"
                                                            ></v-img>
                                                        </v-col>
                                                    </template>
                                                </v-row>
                                                <v-row>
                                                    <v-col
                                                        offset-md="3"
                                                        offset-lg="3"
                                                        cols="12"
                                                        sm="12"
                                                        md="6"
                                                        lg="6"
                                                    >
                                                        <label for="file-input" class="custom-label-button">写真を選ぶ</label>
                                                        <input
                                                            hidden
                                                            id="file-input"
                                                            type="file"
                                                            accept="image/*"
                                                            @change="pushImagePathList($event)"
                                                            ref="image"
                                                        >
                                                        <ErrorMessage :errorMessage="errors.imageList"></ErrorMessage>
                                                    </v-col>
                                                </v-row>
                                                <v-row>
                                                    <v-col
                                                        offset-md="3"
                                                        offset-lg="3"
                                                        cols="12"
                                                        sm="12"
                                                        md="6"
                                                        lg="6"
                                                    >
                                                        <TextField
                                                            label="タイトル"
                                                            v-model="form.title"
                                                            autocomplete
                                                        >
                                                        </TextField>
                                                        <ErrorMessage :errorMessage="errors.title"></ErrorMessage>
                                                    </v-col>
                                                </v-row>
                                                <v-row>
                                                    <v-col
                                                        offset-md="3"
                                                        offset-lg="3"
                                                        cols="12"
                                                        sm="12"
                                                        md="6"
                                                        lg="6"
                                                    >
                                                        <TextArea
                                                            label="レビュー"
                                                            v-model="form.review"
                                                            autocomplete
                                                        >
                                                        </TextArea>
                                                    </v-col>
                                                </v-row>
                                                <v-row>
                                                    <v-col
                                                        offset-md="3"
                                                        offset-lg="3"
                                                        cols="12"
                                                        sm="12"
                                                        md="6"
                                                        lg="6"
                                                    >
                                                        <PullDown
                                                            :typeDivKv="typeDivKv"
                                                            name="ジャンル"
                                                            v-model="form.genreDiv"
                                                            :exceptAll="true"
                                                        >
                                                        </PullDown>
                                                        <ErrorMessage :errorMessage="errors.genreDiv"></ErrorMessage>
                                                    </v-col>
                                                </v-row>
                                                <v-row>
                                                <v-col
                                                    offset-md="3"
                                                    offset-lg="3"
                                                    cols="12"
                                                    sm="12"
                                                    md="6"
                                                    lg="6"
                                                >
                                                    <Button
                                                        name="投稿する"
                                                        color="blue"
                                                        type="submit"
                                                    >
                                                    </Button>
                                                </v-col>
                                            </v-row>
                                            </v-container>
                                        </v-card-text>
                                    </form>
                                </v-card>
                            </v-dialog>
                        </v-row>
                    </template>
                </v-container>
            </v-main>
        </v-container>
    </v-app>
</template>

<style scoped>
.fixed-button {
    position: fixed;
    bottom: 70px;
    right: 70px;
    width: 65px; /* 幅を変更 */
    height: 65px; /* 高さを変更 */
    /* 画面内に続けるための位置調整 */
    z-index: 1000; /* 他の要素の上に表示 */
}

.custom-label-button {
    cursor: pointer; /* マウスオーバー時にカーソルをポインターに変更 */
    padding: 10px 20px; /* ボタンのパディング */
    background-color: #007bff; /* ボタンの背景色 */
    color: #fff; /* ボタンのテキスト色 */
    border: none; /* ボタンのボーダーを削除 */
    border-radius: 5px; /* ボタンの角丸 */
    display: inline-block; /* ボタンをインラインブロックに設定 */
}

.custom-label-button:hover {
    background-color: #0056b3; /* マウスオーバー時の背景色 */
}
</style>
