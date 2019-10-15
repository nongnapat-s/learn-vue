@extends('layouts.app')

@section('title', 'Uploads using JS')

@section('content')
<div id="app" class="col-xs-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3">
    <input
        type="file"
        name="files"
        accept=".xlsx, .xls, .csv"
        multiple
        ref="inputFiles" 
        class="d-none"
        @change="filesChanged">
    @csrf
    {{-- vue directive --}}
    <button
        type="button"
        class="btn btn-info btn-block"
        @click="$refs.inputFiles.click()">
        Browse files
    </button>
    <ul class="list-group my-4">
        <!-- <li 
            v-for="file in files"
            class="list-group-item d-flex justify-content-between align-items-center">
            @{{ file.name }}
            <span 
                v-if="file.size <= 1000000"
                class="badge badge-primary badge-pill">Pass</span>
            <span 
                v-else
                class="badge badge-warning badge-pill">Not allow</span>
        </li> -->
        <li 
            v-for="file in files"
            class="list-group-item d-flex justify-content-between align-items-center">
            @{{ file.content.name }}
            <span 
                :class="'badge badge-pill badge-' + file.state.class">@{{ file.state.label }}</span>
        </li>
    </ul>
    <button
        id="btn-upload"
        type="button"
        class="btn btn-primary btn-block"
        v-show="btnUploadVisible"
        @click="upload">
        Upload
    </button>
</div>
@endsection

@section('extra-script')
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    // page = (html)template + data
    var app = new Vue({ // 1. ให้กำหนด scope application ที่จะเกิดขึ้น ให้อยู่ใน div id app เมื่อกำหนด scope แล้วจะสามารถเข้าถึง vue directive ได้
    el: '#app',
    data: {
       files: [], //content + state
       btnUploadVisible: false
    },
    methods: {  //การจัดการ data
        filesChanged() {
            //this.files = Array.from(this.$refs.inputFiles.files); //ต้องแปลงเป็น array ก่อน เพราะว่า refs ไม่ได้ Implement filter ไว้
            this.files = [];
            Array.from(this.$refs.inputFiles.files).forEach((file) => {
                const state = { label: "Pass", class: "primary"};
                if (file.size > 1000000) {
                    state.label = "Not allow";
                    state.class = "warning";
                }
                this.files.push({
                    content: file,
                    state: state
                })
            });
            this.btnUploadVisible = this.files.filter(file => file.content.size <= 1000000).length > 0; 
            // filter file size น้อยกว่า 1000000 แล้ว check length มากกว่า 0 ให้ แสดงปุ่ม Upload
        },
        upload() {
            this.btnUploadVisible = false;
            this.files.forEach((file) => {
                // console.log(file);
                if (file.content.size > 1000000) return;

                file.state.label = "Sending...";
                file.state.class = "secondary";
                const formData = new FormData();
                formData.append("_token", document.querySelector("input[name=_token]").value);
                formData.append("file", file.content);

                axios.post("/uploads", formData) //simplify
                    .then((response) => {   
                        file.state.label = "Done";
                        file.state.class = "success";
                    }).catch((error) => {
                        file.state.label = "Fail";
                        file.state.class = "danger";
                    })
            });
        }
    }
    })

    // 2. ref เป็น vue directive ที่มี id และ elements
    // 3. test ใน dev console app.$refs.inputFiles
    // 4. vue.js จะเป็น reactivities เมื่อ data เปลี่ยน page เปลี่ยน โดยทำการ render ทันที
</script>
@endsection
