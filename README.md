# Sound Image
Create a video by combining an audio file with an image.  
See blog post: [Share a track on Instagram](http://simon.duhem.fr/blog/lab/share-a-track-on-instagram/).

##How it works?
You have to call the `api.php` file with a `track_id` as parameter.  
Exemple: `http://www.example.com/api.php?track_id=[TRACK_ID]`  

The script will respond a JSON :
```json
{
  "id": "55d394255962c20ba2a4b987c98afd82",
  "track": {
    "id": 89697477,
    "link": "http://www.deezer.com/track/89697477",
    "title": "Jazz et thé vert",
    "artist": "Souleance",
    "album": "La Boulangerie, vol. 3"
  },
  "cover": "http://simon.duhem.fr/lab/sound-image/static/cover/55d394255962c20ba2a4b987c98afd82.jpg",
  "mp3": "http://simon.duhem.fr/lab/sound-image/static/mp3/55d394255962c20ba2a4b987c98afd82.mp3",
  "video": "http://simon.duhem.fr/lab/sound-image/static/video/55d394255962c20ba2a4b987c98afd82.mp4"
}
```

###Files access permissions
- Make sure you can write in the static folders: `chmod 777 static/*`
- This script use a static build of FFmpeg, allow it to be executable: `chmod +x ffmpeg/ffmpeg`
