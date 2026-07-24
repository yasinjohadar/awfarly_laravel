<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setPageId', null)">{{__('pages/pages/edit.content.back')}}</button>
    </div>
    <form wire:submit.prevent="update">
        <div class="form-group" wire:ignore x-data="{contents_en: @entangle('contents_en').defer,}"
             x-init="$nextTick(() => {
                const contents_en_editor = new CKSource.EditorWatchdog();
                window.contents_en_editor = contents_en_editor;
                contents_en_editor.setCreator((element, config) => {
                    return CKSource.Editor
                        .create(element, config)
                        .then(editor => {
                            return editor;
                        })
                });
                contents_en_editor.setDestructor(editor => {
                    return editor.destroy();
                });
                contents_en_editor
                    .create( document.querySelector('#contents_en'), {
                            toolbar: {
                                items: [
                                    'heading',
                                    '|',
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strikethrough',
                                    '|',
                                    'bulletedList',
                                    'numberedList',
                                    '|',
                                    'fontColor',
                                    'fontBackgroundColor',
                                    'fontFamily',
                                    'fontSize',
                                    'highlight',
                                    'removeFormat',
                                    '|',
                                    'alignment',
                                    'outdent',
                                    'indent',
                                    '|',
                                    'undo',
                                    'redo',
                                    'findAndReplace',
                                    'sourceEditing',
                                    'restrictedEditingException',
                                    '-',
                                    'link',
                                    'imageUpload',
                                    'blockQuote',
                                    'insertTable',
                                    'imageInsert',
                                    'mediaEmbed',
                                    '|',
                                    'code',
                                    'codeBlock',
                                    'htmlEmbed',
                                    'pageBreak',
                                    'horizontalLine',
                                    'specialCharacters',
                                    'subscript',
                                    'superscript',
                                    'textPartLanguage'
                                ],
                                shouldNotGroupWhenFull: true
                            },
                            language: '{{App::currentLocale()}}',
                            image: {
                                toolbar: [
                                    'imageTextAlternative',
                                    'imageStyle:inline',
                                    'imageStyle:block',
                                    'imageStyle:side',
                                    'linkImage'
                                ]
                            },
                            table: {
                                contentToolbar: [
                                    'tableColumn',
                                    'tableRow',
                                    'mergeTableCells',
                                    'tableCellProperties',
                                    'tableProperties'
                                ]
                            },
                            autosave: {
                                save( editor ) {
                                    contents_en = editor.getData();
                                    return contents_en;
                                }
                            },
                            licenseKey: ''
                    })
                    .catch( error => {
                        console.log( error );
                    });
            })">
            <label for="contents_en">{{__('pages/pages/edit.content.contents_en')}}</label>
            <textarea rows="10" type="text"
                      class="form-control @error('contents_en') is-invalid @enderror"
                      id="contents_en" x-model="contents_en" x-cloak x-bind:value="contents_en"
                      x-ref="contents_en"
                      wire:model.defer="contents_en"></textarea>
            @error('contents_en')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group" wire:ignore x-data="{contents_ar: @entangle('contents_ar').defer,}"
             x-init="$nextTick(() => {
                const contents_ar_editor = new CKSource.EditorWatchdog();
                window.contents_ar_editor = contents_ar_editor;
                contents_ar_editor.setCreator((element, config) => {
                    return CKSource.Editor
                        .create(element, config)
                        .then(editor => {
                            return editor;
                        })
                });
                contents_ar_editor.setDestructor(editor => {
                    return editor.destroy();
                });
                contents_ar_editor
                    .create( document.querySelector('#contents_ar'), {
                            toolbar: {
                                items: [
                                    'heading',
                                    '|',
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strikethrough',
                                    '|',
                                    'bulletedList',
                                    'numberedList',
                                    '|',
                                    'fontColor',
                                    'fontBackgroundColor',
                                    'fontFamily',
                                    'fontSize',
                                    'highlight',
                                    'removeFormat',
                                    '|',
                                    'alignment',
                                    'outdent',
                                    'indent',
                                    '|',
                                    'undo',
                                    'redo',
                                    'findAndReplace',
                                    'sourceEditing',
                                    'restrictedEditingException',
                                    '-',
                                    'link',
                                    'imageUpload',
                                    'blockQuote',
                                    'insertTable',
                                    'imageInsert',
                                    'mediaEmbed',
                                    '|',
                                    'code',
                                    'codeBlock',
                                    'htmlEmbed',
                                    'pageBreak',
                                    'horizontalLine',
                                    'specialCharacters',
                                    'subscript',
                                    'superscript',
                                    'textPartLanguage'
                                ],
                                shouldNotGroupWhenFull: true
                            },
                            language: '{{App::currentLocale()}}',
                            image: {
                                toolbar: [
                                    'imageTextAlternative',
                                    'imageStyle:inline',
                                    'imageStyle:block',
                                    'imageStyle:side',
                                    'linkImage'
                                ]
                            },
                            table: {
                                contentToolbar: [
                                    'tableColumn',
                                    'tableRow',
                                    'mergeTableCells',
                                    'tableCellProperties',
                                    'tableProperties'
                                ]
                            },
                            autosave: {
                                save( editor ) {
                                    contents_ar = editor.getData();
                                    return contents_ar;
                                }
                            },
                            licenseKey: ''
                    })
                    .catch( error => {
                        console.log( error );
                    });
            })">
            <label for="contents_ar">{{__('pages/pages/edit.content.contents_ar')}}</label>
            <textarea rows="10" type="text"
                      x-model="contents_ar" x-bind:value="contents_ar" x-cloak id="contents_ar"
                      x-ref="contents_ar"
                      class="form-control @error('contents_ar') is-invalid @enderror"
                      wire:model.defer="contents_ar"></textarea>
            @error('contents_ar')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <hr>
        <button wire:loading.attr="disabled" type="submit"
                class="btn btn-primary">{{__('pages/pages/edit.content.submit')}}</button>
    </form>
</div>
