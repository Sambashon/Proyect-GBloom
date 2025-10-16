class Sounds {
    music;
    effects;

    constructor() {
        this.music = [];
        this.effects = [];
        this.repeatMusic = true;
        this.musicVolume = 0.5;
        this.effectsVolume = 0.7;

        this.music.push(this.createAudio("juiceMix","/engine/draftosaurus/music/juiceMix.mp3", true));
        this.effects.push(this.createAudio("place","/engine/draftosaurus/soundEffects/place.wav", false));
    }

    createAudio(name, src, loop) {
        let audio = new Audio(src);
        if (loop) {
            audio.addEventListener('ended', () => {
                if (this.repeatMusic) {
                    audio.currentTime = 0;
                    audio.play();
                }
            });
        }
        return {name: name, audio: audio};
    }

    setEffectsVolume(volume) {
        this.effectsVolume = volume/100;
        this.effects.forEach(effect => {
            effect.audio.volume = this.effectsVolume;
        });
    }

    setMusicVolume(volume) {
        this.musicVolume = volume/100;
        this.music.forEach(theme => {
            theme.audio.volume = this.musicVolume;
        });
    }

    setMasterVolume(volume) {
        this.musicVolume = volume/100 * this.musicVolume;
        this.music.forEach(theme => {
            theme.audio.volume = this.musicVolume;
        });
        this.effectsVolume = volume/100 * this.effectsVolume;
        this.effects.forEach(effect => {
            effect.audio.volume = this.effectsVolume;
        });
    }

    playEffect(name) {
        this.effects.forEach(effect => {
            if (effect.name == name) {
                effect.currentTime = 0;
                effect.audio.play();
            }
        });
    }

    playSoundtrack(name) {
        this.music.forEach(theme => {
            if (theme.name == name) {
                theme.currentTime = 0;
                theme.audio.play();
            }
        });
    }
}
