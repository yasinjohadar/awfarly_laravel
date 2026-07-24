<div>
    <div class="form-group">
        <button class="btn btn-secondary"
                wire:click="$emitUp('setChatId', null)">{{__('pages/community/chats/show.content.back')}}</button>
    </div>
    <div class="form-group">
        <div class="media-chat-scrollable mb-3">
            <div x-data="{messages: {{json_encode($messages)}}, users: {{json_encode($users)}}}"
                 x-init="$nextTick(() => {
                 console.log(users);
                    $('.media-chat-scrollable').scroll(function(){
                        if ($(this).prop('offsetHeight') - $(this).prop('scrollHeight') === $(this).prop('scrollTop')){
                            @this.loadMore();
                        }
                    });
                    let db = firebase.firestore();
                    const vm = this;
                    return new Promise((resolve, reject) => {
                        let groupRef = db.collection('chats')
                        .doc(messages[0].channel.token)
                        .onSnapshot(function (querySnapshot) {
                          let data = querySnapshot.data();
                          if(data.id !== messages[messages.length -1].id){
                            messages.push(data);
                          }
                        })
                    })
                 })">
                <ul class="media-list media-chat">
                    <template x-if="messages.length > 0">
                        <div>
                            <template x-for="message in messages" :key="message.id">
                                <div>
                                    <template x-el="ul" x-if="message.owner.type === users.left_user.type && message.owner.id === users.left_user.id">
                                        {{--Left Message--}}
                                        <li class="media">
                                            <div class="mr-3">
                                                <img
                                                    :src="message.owner.imageUrl ?? '{{route('users.profile.image', null)}}'"
                                                    :title="message.owner.name"
                                                    :alt="message.owner.name"
                                                    class="rounded-circle"
                                                    width="40" height="40">
                                            </div>
                                            {{--Message--}}
                                            <div class="media-body">
                                                <div class="media-chat-item">
                                                    <a class="text-secondary" :href="users.left_user.url" x-text="message.owner.name" target="_blank"></a>
                                                    <div x-text="message.message"></div>
                                                    <template x-if="message.media != null">
                                                        <div class="mt-2">
                                                            <template x-if="message.media.type === 'image'">
                                                                <img :src="message.media.mediaUrl"
                                                                     :alt="message.media.fileName"
                                                                     class="img-fluid"
                                                                     width="240"/>
                                                            </template>

                                                            <template x-if="message.media.type === 'video'">
                                                                <video width="320" height="240" controls>
                                                                    <source :src="message.media.mediaUrl"
                                                                            :alt="message.media.fileName"
                                                                            :type="message.media.mimeType">
                                                                </video>
                                                            </template>

                                                            <template x-if="message.media.type === 'audio'">
                                                                <audio width="320" height="240" controls>
                                                                    <source :src="message.media.mediaUrl"
                                                                            :alt="message.media.fileName"
                                                                            :type="message.media.mimeType">
                                                                </audio>
                                                            </template>

                                                            <template x-if="message.media.type === 'file'">
                                                                <a :href="message.media.downloadUrl"
                                                                   x-text="message.media.fileName"
                                                                   :alt="message.media.fileName" target="_blank"/>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="font-size-sm text-muted mt-2"
                                                     x-text="message.createdAt"></div>
                                            </div>
                                        </li>
                                    </template>
                                    <template x-el="ul" x-if="message.owner.type === users.right_user.type && message.owner.id === users.right_user.id">
                                        {{--Right Message--}}
                                        <li class="media media-chat-item-reverse">
                                            <div class="media-body">
                                                <div class="media-chat-item">
                                                    <a class="text-dark" :href="users.right_user.url" x-text="message.owner.name" target="_blank"></a>
                                                    <div x-text="message.message"></div>
                                                    <template x-if="message.media != null">
                                                        <div class="mt-2">
                                                            <template x-if="message.media.type === 'image'">
                                                                <img :src="message.media.mediaUrl"
                                                                     :alt="message.media.fileName"
                                                                     class="img-fluid"
                                                                     width="240"/>
                                                            </template>

                                                            <template x-if="message.media.type === 'video'">
                                                                <video width="320" height="240" controls>
                                                                    <source :src="message.media.mediaUrl"
                                                                            :alt="message.media.fileName"
                                                                            :type="message.media.mimeType">
                                                                </video>
                                                            </template>

                                                            <template x-if="message.media.type === 'audio'">
                                                                <audio width="320" height="240" controls>
                                                                    <source :src="message.media.mediaUrl"
                                                                            :alt="message.media.fileName"
                                                                            :type="message.media.mimeType">
                                                                </audio>
                                                            </template>

                                                            <template x-if="message.media.type === 'file'">
                                                                <a class="text-dark"
                                                                   :href="message.media.downloadUrl"
                                                                   x-text="message.media.fileName"
                                                                   :alt="message.media.fileName"/>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div class="font-size-sm text-muted mt-2"
                                                     x-text="message.createdAt"></div>
                                            </div>
                                            {{--Avatar--}}
                                            <div class="ml-3">
                                                <img :title="message.owner.name"
                                                     :src="message.owner.imageUrl ?? '{{route('users.profile.image', null)}}'"
                                                     :alt="message.owner.name"
                                                     class="rounded-circle"
                                                     width="40" height="40">
                                            </div>
                                        </li>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="messages.length === 0">
                        <div class="py-4 text-gray-600">
                            {{__('pages/community/chats/show.content.no-messages')}}
                        </div>
                    </template>
                </ul>
            </div>
        </div>
    </div>
</div>
