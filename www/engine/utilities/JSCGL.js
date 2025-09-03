class Matrix3D {
    static multiplyMatrices(a, b) {
        let result = new Float32Array(16);
        for (let row = 0; row < 4; row++) {
            for (let col = 0; col < 4; col++) {
                let sum = 0;
                for (let i = 0; i < 4; i++) {
                    sum += b[i + row * 4] * a[col + i * 4];
                }
                result[col + row * 4] = sum;
            }
        }
        return result;
    }

    static multiplyMatrixVector(matrix, vector) {
        let result = [];
        for (let row = 0; row < 4; row++) {
            let sum = 0;
            for (let i = 0; i < 4; i++) {
                sum += matrix[row + i * 4] * vector[i];
            }
            result.push(sum);
        }
        return result;
    }

    static invertMatrix(m) {
        const inv = new Float32Array(16);
        const [
            m00, m01, m02, m03,
            m10, m11, m12, m13,
            m20, m21, m22, m23,
            m30, m31, m32, m33
        ] = m;

        inv[0] = m11*m22*m33 - m11*m23*m32 - m21*m12*m33 + m21*m13*m32 + m31*m12*m23 - m31*m13*m22;
        inv[1] = -m01*m22*m33 + m01*m23*m32 + m21*m02*m33 - m21*m03*m32 - m31*m02*m23 + m31*m03*m22;
        inv[2] = m01*m12*m33 - m01*m13*m32 - m11*m02*m33 + m11*m03*m32 + m31*m02*m13 - m31*m03*m12;
        inv[3] = -m01*m12*m23 + m01*m13*m22 + m11*m02*m23 - m11*m03*m22 - m21*m02*m13 + m21*m03*m12;

        inv[4] = -m10*m22*m33 + m10*m23*m32 + m20*m12*m33 - m20*m13*m32 - m30*m12*m23 + m30*m13*m22;
        inv[5] = m00*m22*m33 - m00*m23*m32 - m20*m02*m33 + m20*m03*m32 + m30*m02*m23 - m30*m03*m22;
        inv[6] = -m00*m12*m33 + m00*m13*m32 + m10*m02*m33 - m10*m03*m32 - m30*m02*m13 + m30*m03*m12;
        inv[7] = m00*m12*m23 - m00*m13*m22 - m10*m02*m23 + m10*m03*m22 + m20*m02*m13 - m20*m03*m12;

        inv[8] = m10*m21*m33 - m10*m23*m31 - m20*m11*m33 + m20*m13*m31 + m30*m11*m23 - m30*m13*m21;
        inv[9] = -m00*m21*m33 + m00*m23*m31 + m20*m01*m33 - m20*m03*m31 - m30*m01*m23 + m30*m03*m21;
        inv[10] = m00*m11*m33 - m00*m13*m31 - m10*m01*m33 + m10*m03*m31 + m30*m01*m13 - m30*m03*m11;
        inv[11] = -m00*m11*m23 + m00*m13*m21 + m10*m01*m23 - m10*m03*m21 - m20*m01*m13 + m20*m03*m11;

        inv[12] = -m10*m21*m32 + m10*m22*m31 + m20*m11*m32 - m20*m12*m31 - m30*m11*m22 + m30*m12*m21;
        inv[13] = m00*m21*m32 - m00*m22*m31 - m20*m01*m32 + m20*m02*m31 + m30*m01*m22 - m30*m02*m21;
        inv[14] = -m00*m11*m32 + m00*m12*m31 + m10*m01*m32 - m10*m02*m31 - m30*m01*m12 + m30*m02*m11;
        inv[15] = m00*m11*m22 - m00*m12*m21 - m10*m01*m22 + m10*m02*m21 + m20*m01*m12 - m20*m02*m11;

        // Determinante
        const det = m00*inv[0] + m01*inv[4] + m02*inv[8] + m03*inv[12];
        if (det === 0) return null;

        for (let i = 0; i < 16; i++) inv[i] /= det;
        return inv;
    }

    static scaleMatrix(sx, sy, sz) {
        return new Float32Array([
            sx, 0,  0,  0,
            0,  sy, 0,  0,
            0,  0,  sz, 0,
            0,  0,  0,  1
        ]);
    }

    static rotationYMatrix(angle) {
        const c = Math.cos(angle);
        const s = Math.sin(angle);
    
        return new Float32Array([
             c,  0, s, 0,
             0,  1, 0, 0,
            -s,  0, c, 0,
             0,  0, 0, 1
        ]);
    }

    static rotationXMatrix(angle) {
        const c = Math.cos(angle);
        const s = Math.sin(angle);
    
        return new Float32Array([
            1, 0, 0, 0,
            0, c, s, 0,
            0, -s, c, 0,
            0, 0, 0, 1
        ]);
    }

    static rotationZMatrix(angle) {
        const c = Math.cos(angle);
        const s = Math.sin(angle);
    
        return new Float32Array([
            c, s, 0, 0,
            -s, c, 0, 0,
            0, 0, 1, 0,
            0, 0, 0, 1
        ]);
    }

    static subtract(a, b) {
        return new Float32Array([
            a[0] - b[0],
            a[1] - b[1],
            a[2] - b[2]
        ]);
    }

    static normalize(v) {
        const len = Math.hypot(v[0], v[1], v[2]);
        if (len > 0) {
            return new Float32Array([
                v[0] / len,
                v[1] / len,
                v[2] / len
            ]);
        }
        return new Float32Array([0, 0, 0]);
    }

    static cross(a, b) {
        return new Float32Array([
            a[1] * b[2] - a[2] * b[1],
            a[2] * b[0] - a[0] * b[2],
            a[0] * b[1] - a[1] * b[0]
        ]);
    }

    static dot(a, b) {
        return a[0] * b[0] + a[1] * b[1] + a[2] * b[2];
    }

    static lookAt(eye, center, up) {
        const zAxis = this.normalize(this.subtract(eye, center));
        const xAxis = this.normalize(this.cross(up, zAxis));
        const yAxis = this.cross(zAxis, xAxis);
    
        return new Float32Array([
            xAxis[0], yAxis[0], zAxis[0], 0,
            xAxis[1], yAxis[1], zAxis[1], 0,
            xAxis[2], yAxis[2], zAxis[2], 0,
            -this.dot(xAxis, eye),
            -this.dot(yAxis, eye),
            -this.dot(zAxis, eye),
            1
        ]);
    }

    static perspectiveMatrix(fovRad, aspect, near, far) {
        const f = 1.0 / Math.tan(fovRad / 2);
        const nf = 1 / (near - far);
    
        return new Float32Array([
            f / aspect, 0, 0, 0,
            0, f, 0, 0,
            0, 0, (far + near) * nf, -1,
            0, 0, (2 * far * near) * nf, 0
        ]);
    }

    static orthographicMatrix(left, right, bottom, top, near, far) {
        const lr = 1 / (left - right);
        const bt = 1 / (bottom - top);
        const nf = 1 / (near - far);

        return new Float32Array([
            2 * lr, 0,       0,              0,
            0,      2 * bt,  0,              0,
            0,      0,      -2 * nf,         0,
            -(right + left) * lr,
            -(top + bottom) * bt,
            -(far + near) * nf,
            1
        ]);
    }





    static translationMatrix(tx, ty, tz) {
        return new Float32Array([
            1, 0, 0, 0,
            0, 1, 0, 0,
            0, 0, 1, 0,
            tx, ty, tz, 1
        ]);
    }
    

    static matTRS(tx, ty, tz, eyeRotation, rotation,  sx, sy, sz) {
        const T = this.translationMatrix(tx, ty, tz);
        const fromEyeR = this.multiplyMatrices(this.rotationXMatrix(eyeRotation[2]),this.multiplyMatrices(this.rotationYMatrix(eyeRotation[1]),this.rotationZMatrix(eyeRotation[0])));
        const fromShapeR = this.multiplyMatrices(this.rotationXMatrix(rotation[2]),this.multiplyMatrices(this.rotationYMatrix(rotation[1]),this.rotationZMatrix(rotation[0])));
        const S = this.scaleMatrix(sx, sy, sz);

        if (eyeRotation) {
            return this.multiplyMatrices(fromEyeR ,this.multiplyMatrices(T, this.multiplyMatrices(fromShapeR, S)));
        }
        else {
            return this.multiplyMatrices(T, this.multiplyMatrices(fromShapeR, S));
        }
    }

    static newMat4() {
        return new Float32Array([
            1, 0, 0, 0,
            0, 1, 0, 0,
            0, 0, 1, 0,
            0, 0, 0, 1
        ]);
    }

    static convertToRad(angle) {
        return angle * Math.PI / 180;
    }
    
    static convertToDeg(angle) {
        return angle * 180.0 / Math.PI;
    }

    static vec3fromValues(x, y, z) {
        return new Float32Array([x, y, z]);
    }

}

class Line3D {
    id;
    type;
    color;
    position;
    rotation;
    start;
    end;
    thickness;

    constructor({id: id, start: start, end: end, thickness: thickness, color: color}) {
        this.id = id;
        this.start = start;
        this.end = end;
        this.type = "line";
        this.thickness = thickness;
        this.position = [start[0]+((end[0]-start[0])/2), start[1]+((end[1]-start[1])/2), start[2]+((end[2]-start[2])/2)];
        this.rotation = [0, 0, 0];
        this.color = color || [1, 1, 1, 1];
    }

    draw(jgl, pass, camera, matViewProjBuffer) {
        let eye = camera.position;
        let eyeRot = camera.rotation;

        const matFromEyeWorld = Matrix3D.matTRS(this.position[0]-eye[0], this.position[1]-eye[1], this.position[2]-eye[2], [eyeRot[0], eyeRot[1], eyeRot[2]], [this.rotation[0], this.rotation[1], this.rotation[2]], 1, 1, 1);
        const viewport = new Float32Array([jgl.width, jgl.height]);

        const line = new ArrayBuffer(64);
        const lineDataView = new DataView(line);

        lineDataView.setFloat32(0, this.start[0], true);
        lineDataView.setFloat32(4, this.start[1], true);
        lineDataView.setFloat32(8, this.start[2], true);
        lineDataView.setFloat32(16, this.end[0], true);
        lineDataView.setFloat32(20, this.end[1], true);
        lineDataView.setFloat32(24, this.end[2], true);
        lineDataView.setFloat32(32, this.color[0], true);
        lineDataView.setFloat32(36, this.color[1], true);
        lineDataView.setFloat32(40, this.color[2], true);
        lineDataView.setFloat32(48, this.thickness, true);

        jgl.setPipeline("line");

        const lineDataBuffer = jgl.createUniformBuffer(line);
        const viewportBuffer = jgl.createUniformBuffer(viewport);
        const matFromEyeWorldBuffer = jgl.createUniformBuffer(matFromEyeWorld);
        const matBindGroup = jgl.createUniformBindGroup(1, [[matFromEyeWorldBuffer, 1], [matViewProjBuffer, 2], [viewportBuffer, 7]]);
        const lineBindGroup = jgl.createUniformBindGroup(2, [[lineDataBuffer, 0]]);

        pass.setPipeline(jgl.actualPipeline);
        pass.setBindGroup(1, matBindGroup.bindGroup);
        pass.setBindGroup(2, lineBindGroup.bindGroup);
        pass.draw(6, 1);
    }

    update(dt) {
        return undefined;
    }
}

class Object3D {
    id;
    position;
    velocity;
    indices;
    inverted;
    invertNormalsUniform;
    size;
    rotation;
    vao;
    texture;

    constructor (id, position, velocity, size, rotation, model, inverted) {
        this.id = id;
        this.position = position;
        this.velocity = velocity;
        this.size = size;
        this.rotation = rotation;
        this.model = model;
        this.inverted = inverted;
    }

    update(dt) {
        this.position[0] += this.velocity[0] * dt;
        this.position[1] += this.velocity[1] * dt;
        this.position[2] += this.velocity[2] * dt;
    }

    draw(jgl, pass, camera, matViewProjBuffer, inverted, pointLights) {
        let light = [0, 10, 0];
        let lightRot = [0, 0, 0];
        let eye = camera.position;
        let eyeRot = camera.rotation;
        const matFromLightWorld = Matrix3D.matTRS(this.position[0]-light[0], this.position[1]-light[1], this.position[2]-light[2], [lightRot[0], lightRot[1], lightRot[2]], [this.rotation[0], this.rotation[1], this.rotation[2]], this.size[0], this.size[1], this.size[2]);
        const matFromEyeWorld = Matrix3D.matTRS(this.position[0]-eye[0], this.position[1]-eye[1], this.position[2]-eye[2], [eyeRot[0], eyeRot[1], eyeRot[2]], [this.rotation[0], this.rotation[1], this.rotation[2]], this.size[0], this.size[1], this.size[2]);
        const matWorld = Matrix3D.matTRS(this.position[0], this.position[1], this.position[2], 0, [this.rotation[0], this.rotation[1], this.rotation[2]], this.size[0], this.size[1], this.size[2]);

        const invertNormals = new Float32Array([inverted]);
        const numPointLights = new Uint32Array([pointLights.length]);
        const cameraView = new Float32Array(eye);

        /*
            @group(1) @binding(0) var<uniform> matWorld: mat4x4<f32>;
            @group(1) @binding(1) var<uniform> matFromEyeWorld: mat4x4<f32>;
            @group(1) @binding(2) var<uniform> matViewProj: mat4x4<f32>;
            @group(1) @binding(3) var<uniform> cameraView: vec3f;
            @group(1) @binding(4) var<uniform> lightPoint: array<PointLight, 8>;
            @group(1) @binding(5) var<uniform> numPointLights: u32;
            @group(1) @binding(6) var<uniform> invertNormals: f32;
        */
        
        /*
        /* El struct de PointLight en el shader tiene 4 atributos: position, color, diffuse y specular.
        WebGPU precisa que el buffer que le pase estos datos esté alineado en cantidad bytes que sea 4, 8, 16.
        Es decir que si los bytes entregados en el buffer no cumplen esa alineación, o uno, se hace de
        forma automática en casos especificos como al establecer un vec3 (12 bytes se alinea a 16), o dos,
        da error porque la GPU lee en bloques los cuales deben estar alineados en cantidad de bytes para su
        correcto y rápido funcionamiento. Por esto mismo debemos asegurarnos que el struct esté alineado.
        En el caso de los structs la alineación va en bloques de 16, osea que la cantidad de bytes se pueda
        representar con 16 * n. position y color cumplen eso porque son vec3f que se alinean automaticamente
        a 16, pero luego tenemos dos f32 que ocupan 8 bytes en total. Para poder alinearlo debemos agregarle
        unos 8 bytes de padding, quedanonos asi:

        position => vec3f => 16
        color => vec3f => 16
        diffuse => f32 => 4
        specular => f32 => 4
        Total = 40 bytes + 8 padding = 48 bytes

        Nuestro lightPoint uniform en el shader tiene 8 elementos PointLight, es decir 48 * 8 cantidad de bytes.
        Eso nos da un total de 384 bytes que nuestro buffer debe ocupar. Para poder agregar al buffer todos los elementos,
        mas su respectivo padding, usaremos ArrayBuffer, el cual es un array tipado de JavaScript, especifico
        para bloques binarios. A su vez para utilizarlo usaremos un DataView.
        */ 

        const structSize = 48;
        const lightPointsArray = new ArrayBuffer(structSize * 8);
        const lightsDataView = new DataView(lightPointsArray);

        for (let i = 0; i < pointLights.length; i++) {
            const offset = i * structSize; 
            const element = pointLights[i]
            lightsDataView.setFloat32(offset + 0, element.position[0], true);
            lightsDataView.setFloat32(offset + 4, element.position[1], true);
            lightsDataView.setFloat32(offset + 8, element.position[2], true);

            lightsDataView.setFloat32(offset + 16, element.color[0], true);
            lightsDataView.setFloat32(offset + 20, element.color[1], true);
            lightsDataView.setFloat32(offset + 24, element.color[2], true);

            lightsDataView.setFloat32(offset + 32, element.diffuseStrength, true);
            lightsDataView.setFloat32(offset + 36, element.specularStrength, true);
        }

        jgl.setPipeline("triangle");

        const depthTextureBindGroup = jgl.device.createBindGroup({
            layout: jgl.actualPipeline.getBindGroupLayout(3),
            entries: [
                {binding: 0, resource: jgl.device.createSampler({compare: "less",
                    minFilter: "linear",
                    magFilter: "linear"
                })},
                {binding: 1, resource: jgl.shadowMap.createView()}
            ]
        });

        const orthographicMatrix = Matrix3D.orthographicMatrix(180, -180, 100, -100, 0.1, 1000);
        const matShadowProjBuffer = jgl.createUniformBuffer(orthographicMatrix);

        const invertNormalsBuffer = jgl.createUniformBuffer(invertNormals);
        const numPointLightsBuffer = jgl.createUniformBuffer(numPointLights);
        const lightPointBuffer = jgl.createUniformBuffer(lightPointsArray);
        const cameraViewBuffer = jgl.createUniformBuffer(cameraView);
        const matWorldBuffer = jgl.createUniformBuffer(matWorld);
        const matFromEyeWorldBuffer = jgl.createUniformBuffer(matFromEyeWorld);
        const matFromLightWorldBuffer = jgl.createUniformBuffer(matFromLightWorld);
        const matBindGroup = jgl.createUniformBindGroup(1, [[matWorldBuffer, 0], [matFromEyeWorldBuffer, 1], [matViewProjBuffer, 2], [cameraViewBuffer, 3], [lightPointBuffer, 4], [numPointLightsBuffer, 5], [invertNormalsBuffer, 6], [matFromLightWorldBuffer, 8], [matShadowProjBuffer, 9]]);

        pass.setPipeline(jgl.actualPipeline);
        pass.setBindGroup(0, this.model.textureBindGroup.bindGroup);
        pass.setBindGroup(1, matBindGroup.bindGroup);
        pass.setBindGroup(3, depthTextureBindGroup);
        pass.setVertexBuffer(0, this.model.vertexBuffer);
        pass.setVertexBuffer(1, this.model.vertexBuffer);
        pass.setVertexBuffer(2, this.model.vertexBuffer);
        pass.draw(this.model.vertices);
    }
    
    renderShadowMap(jgl, pass, lightPoint, matViewProjBuffer) {
        let light = [0, 10, 0];
        let lightRot = [0, 0, 0];
        const matFromLightWorld = Matrix3D.matTRS(this.position[0]-light[0], this.position[1]-light[1], this.position[2]-light[2], [lightRot[0], lightRot[1], lightRot[2]], [this.rotation[0], this.rotation[1], this.rotation[2]], this.size[0], this.size[1], this.size[2]);
        const matWorld = Matrix3D.matTRS(this.position[0], this.position[1], this.position[2], 0, [this.rotation[0], this.rotation[1], this.rotation[2]], this.size[0], this.size[1], this.size[2]);

        const lightPosition = new Float32Array(light);

        jgl.setPipeline("depth");
        pass.setPipeline(jgl.actualPipeline);

        const emptyBindGroup = jgl.device.createBindGroup({
            layout: jgl.actualPipeline.getBindGroupLayout(0),
            entries: []
        });

        const orthographicMatrix = Matrix3D.orthographicMatrix(180, -180, 100, -100, 0.1, 1000);
        const matShadpwProjBuffer = jgl.createUniformBuffer(orthographicMatrix);

        const lightPositionBuffer = jgl.createUniformBuffer(lightPosition);
        const matWorldBuffer = jgl.createUniformBuffer(matWorld);
        const matFromLightWorldBuffer = jgl.createUniformBuffer(matFromLightWorld);
        const matBindGroup = jgl.createUniformBindGroup(1, [/*[matWorldBuffer, 0],*/ [matFromLightWorldBuffer, 1], [matShadpwProjBuffer, 2]/*, [lightPositionBuffer, 3]*/]);

        pass.setBindGroup(1, matBindGroup.bindGroup);
        pass.setBindGroup(0, emptyBindGroup);
        pass.setVertexBuffer(0, this.model.vertexBuffer);
        pass.draw(this.model.vertices);
    }

    getRotationDeg() {
        return [Matrix3D.convertToDeg(this.rotation[0]), Matrix3D.convertToDeg(this.rotation[1]), Matrix3D.convertToDeg(this.rotation[2])];
    }

    setRotationDeg(rotation) {
        this.rotation = [Matrix3D.convertToRad(rotation[0]), Matrix3D.convertToRad(rotation[1]), Matrix3D.convertToRad(rotation[2])];
    }
}

class Model {
    vertices;
    indices;
    texture;
    specularMap;

    constructor (model) {
        /*
            For now this will be obsolete
        if (indices) {
            this.indices = indices;
        }
        else {
        */
        this.indices = this.autoIndices(model.vertexData);
        //}
        this.vertices = model.vertexData;
        this.texture = model.textureData;
        this.specularMap = model.specularMap;
    }

    autoIndices(vertices) {
        let array_temp = [];
        for (let i = 0; i < vertices.length/8; i++) {
            array_temp.push(i);
        }
        return new Uint16Array(array_temp);
    }
}

class Scene {
    objects;
    models;
    sceneData;
    cameras;
    invertedObjects;
    actualCamera;
    pointLights;
    textures;

    // Auxiliar Attributes
    matView;
    matViewProj = Matrix3D.newMat4();
    matViewProjBuffer;

    constructor(scene) {
        this.sceneData = scene;
        this.objects = [];
        this.models = [];
        this.textures = [];
        this.actualCamera = "camera00"
        this.pointLights = [];
        this.cameras = [];
        this.invertedObjects = [];
        this.matView = Matrix3D.lookAt(
        Matrix3D.vec3fromValues(0, 0, 0),
        Matrix3D.vec3fromValues(0, 0, -1),
        Matrix3D.vec3fromValues(0, 1, 0));
    }

    async loadScene(jgl) {
        let fetchImage = new Image();
        let modelData = [];
        let objects = [];
        let pointLights = [];
        let invertedObjects = [];
        let cameras = [];

        jgl.setPipeline("triangle");


        if (this.sceneData.src.models) {
            for (let i = 0; i < this.sceneData.src.models.length; i++) {
                const actualModel = this.sceneData.src.models[i];
                let specularMapImageBitmap = null;
                fetchImage.src = this.sceneData.src.images[actualModel.image];
                await fetchImage.decode();
                let textureImageBitmap = await createImageBitmap(fetchImage);
                this.textures.push({id: actualModel.id, ImageBitmap: textureImageBitmap});

                if (this.sceneData.src.images[actualModel.specularMap]) {
                    fetchImage.src = this.sceneData.src.images[actualModel.specularMap];
                    await fetchImage.decode();
                    specularMapImageBitmap = await createImageBitmap(fetchImage);
                    this.textures.push({id: actualModel.id, ImageBitmap: specularMapImageBitmap});
                    modelData.push({id: actualModel.id, model: new Model(await this.fetchModel(actualModel.model, textureImageBitmap, specularMapImageBitmap))});
                } else {
                    modelData.push({id: actualModel.id, model: new Model(await this.fetchModel(actualModel.model, textureImageBitmap))});
                }

            }
            
            for (let i = 0; i < modelData.length; i++) {
                this.models.push({id: modelData[i].id, model: jgl.loadModel(modelData[i].model)});
            }

            for (let i = 0; i < this.sceneData.data.objects.length; i++) {
                let actualObject = this.sceneData.data.objects[i];
                if (actualObject.type != "line") {
                    objects.push(jgl.newObject({id: actualObject.id, model: this.models[actualObject.model].model, position: actualObject.position, size: actualObject.scale, velocity: actualObject.velocity, rotation: actualObject.rotation, inverted: actualObject.inverted}));
                } else {   
                    objects.push(jgl.newLine({id: actualObject.id, type: actualObject.type, start: actualObject.start, end: actualObject.end, thickness: actualObject.thickness, color: actualObject.color}));
                }
            }
            objects.sort();

            for (let i = 0; i < objects.length; i++) {
                invertedObjects.push(objects[i].inverted);
            }
        }

        if (this.sceneData.data.pointLights) {
            for (let i = 0; i < this.sceneData.data.pointLights.length; i++) {
                let actualLight = this.sceneData.data.pointLights[i];
                pointLights.push({id: actualLight.id, position: actualLight.position, color: actualLight.color, diffuseStrength: actualLight.diffuseStrength, specularStrength: actualLight.diffuseStrength});
            }
        }

        if (this.sceneData.data.cameras) {
            for (let i = 0; i < this.sceneData.data.cameras.length; i++) {
                let actualCamera = this.sceneData.data.cameras[i];
                cameras.push({id: actualCamera.id, position: actualCamera.position, rotation: actualCamera.rotation, fov: actualCamera.fov, active: actualCamera.active});
            }
        }

        this.objects = objects;
        this.invertedObjects = invertedObjects;
        this.cameras = cameras.sort();
        this.pointLights = pointLights.sort();
    }

    draw(jgl, dt, width, height) {
        let camera;
        for (let i = 0; i < this.cameras.length; i++) {
            if (this.cameras[i].id == this.actualCamera) {
                camera = this.cameras[i];
                if (!camera.active) {
                    camera.active = true;
                    
                    const matProj = Matrix3D.perspectiveMatrix(
                        Matrix3D.convertToRad(camera.fov),
                        width / height,
                        0.1, 1000.0
                    );

                    this.matViewProj = Matrix3D.multiplyMatrices(matProj, this.matView);
                    this.matViewProjBuffer = jgl.createUniformBuffer(this.matViewProj);
                    break;
                }
            }
        }

        let encoder = jgl.device.createCommandEncoder();
        let depthencoder = jgl.device.createCommandEncoder();

        let pass = encoder.beginRenderPass({
            colorAttachments: [{
                view: jgl.context.getCurrentTexture().createView(),
                loadOp: "clear",
                clearValue: [BACKGROUND_COLOR[0], BACKGROUND_COLOR[1], BACKGROUND_COLOR[2], BACKGROUND_COLOR[3]],
                storeOp: "store"
            }],
            depthStencilAttachment: {
                view: jgl.depthTexture.createView(),
                depthLoadOp: 'clear',
                depthClearValue: 1.0,
                depthStoreOp: 'store',
            },
            primitive: {
                topology: "triangle-list"
            }
        });

        let depthpass = depthencoder.beginRenderPass({
            colorAttachments: [],
            depthStencilAttachment: {
                view: jgl.shadowMap.createView(),
                depthLoadOp: 'clear',
                depthClearValue: 1.0,
                depthStoreOp: 'store',
            }
        });

        for (let i = 0; i < this.objects.length; i++) {
            if (this.objects[i].type != "line" && !this.invertedObjects[i]) {
                this.objects[i].renderShadowMap(jgl, depthpass, camera, this.matViewProjBuffer);
            }
        }
        depthpass.end();

        for (let i = 0; i < this.objects.length; i++) {
            this.objects[i].update(dt);
            if (this.invertedObjects[i] == true) {
                this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer, -1, this.pointLights );
            }
            else {
                if (this.objects[i].type != "line") {
                    this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer, 1, this.pointLights);
                } else {
                    this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer);
                }
            }
            
        }

        pass.end();

        jgl.device.queue.submit([depthencoder.finish(), encoder.finish()]);
    }

    screenshot(jgl, dt, width, height) {
        let screenshot = jgl.device.createTexture({
            size: [width, height],
            format: "bgra8unorm",
            usage: GPUTextureUsage.RENDER_ATTACHMENT | GPUTextureUsage.COPY_SRC
        });
        let camera;
        for (let i = 0; i < this.cameras.length; i++) {
            if (this.cameras[i].id == this.actualCamera) {
                camera = this.cameras[i];
                if (!camera.active) {
                    camera.active = true;
                    
                    let matProj = Matrix3D.perspectiveMatrix(
                        Matrix3D.convertToRad(camera.fov),
                        width / height,
                        0.1, 1000.0
                    );

                    let matViewProj = Matrix3D.multiplyMatrices(matProj, this.matView);
                    this.matViewProjBuffer = jgl.createUniformBuffer(matViewProj);
                    break;
                }
            }
        }

        let encoder = jgl.device.createCommandEncoder();
        let depthencoder = jgl.device.createCommandEncoder();

        let pass = encoder.beginRenderPass({
            colorAttachments: [{
                view: screenshot.createView(),
                loadOp: "clear",
                clearValue: [0,0,0,0],
                storeOp: "store"
            }],
            depthStencilAttachment: {
                view: jgl.depthTexture.createView(),
                depthLoadOp: 'clear',
                depthClearValue: 1.0,
                depthStoreOp: 'store',
            },
            primitive: {
                topology: "triangle-list"
            }
        });

        let depthpass = depthencoder.beginRenderPass({
            colorAttachments: [],
            depthStencilAttachment: {
                view: jgl.shadowMap.createView(),
                depthLoadOp: 'clear',
                depthClearValue: 1.0,
                depthStoreOp: 'store',
            }
        });

        for (let i = 0; i < this.objects.length; i++) {
            if (this.objects[i].type != "line" && !this.invertedObjects[i]) {
                this.objects[i].renderShadowMap(jgl, depthpass, camera, this.matViewProjBuffer);
            }
        }
        depthpass.end();

        for (let i = 0; i < this.objects.length; i++) {
            this.objects[i].update(dt);
            if (this.invertedObjects[i] == true) {
                this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer, -1, this.pointLights );
            }
            else {
                if (this.objects[i].type != "line") {
                    this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer, 1, this.pointLights);
                } else {
                    this.objects[i].draw(jgl, pass, camera, this.matViewProjBuffer);
                }
            }
            
        }

        pass.end();

        jgl.device.queue.submit([depthencoder.finish(), encoder.finish()]);
        return screenshot;
    }

    newCamera(position, rotation, active) {
        if (!active) {
            active = false;
        }
        this.cameras.push([position, rotation, active]);
    }

    /*
    Binary Search future implementation
    getObjectById(id) {
        let first = 0;
        let last = this.objects.length - 1;
        let actual;
        while (first < last) {
            actual = parseInt((first + last) / 2);
            const element = this.objects[actual].id;

            if (element === id) {
                return actual;
            }
            else if (element < id) {
                first++;
            }
            else if (element > id) {
                last--;
            }
        }
        return false;
    }
    */

    getObjectById(id) {
        let searchedObject;
        this.objects.forEach(object => {
            if (object.id == id) {
                searchedObject = object
            }
        });

        return searchedObject;
    }

    getModelById(id) {
        let searchedModel;
        this.models.forEach(model => {
            if (model.id == id) {
                searchedModel = model;
            }
        })

        return searchedModel;
    }

    push(object, inverted) {
        if (typeof inverted != 'boolean') {
            inverted = false;
        }
        this.objects.push(object);
        this.invertedObjects.push(inverted);
    }

    unshift(object, inverted) {
        if (typeof inverted != 'boolean') {
            inverted = true;
        }
        this.objects.push(object);
        this.invertedObjects.push(inverted);
    }

    pop() {
        this.objects.pop();
    }

    shift() {
        this.objects.shift();
    }

    objToArray(obj) {
        const vertices = [];
        const uvs = [];
        const normals = [];
        const model = [];

        const lines = obj.split('\n');

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();

            if (line.startsWith('v ')) {
                const [, x, y, z] = line.split(/\s+/);
                vertices.push([parseFloat(x), parseFloat(y), parseFloat(z)]);
            } else if (line.startsWith('vt ')) {
                const [, u, v] = line.split(/\s+/);
                uvs.push([parseFloat(u), 1 - parseFloat(v)]);
            } else if (line.startsWith('vn ')) {
                const [, x, y, z] = line.split(/\s+/);
                normals.push([parseFloat(x), parseFloat(y), parseFloat(z)]);
            } else if (line.startsWith('f ')) {
                const parts = line.split(/\s+/).slice(1);
                for (let j = 0; j < parts.length; j++) {
                    const [v, vt, vn] = parts[j].split('/').map(function (i) {return parseInt(i) - 1;});
                    const vert = vertices[v];
                    const uv = uvs[vt];
                    const norm = normals[vn];

                    model.push(...vert, ...uv, ...norm);
                }
            }
        }

        return model;
    }
    
    async fetchModel(file, texture, specularMap, indices) {
        let response = await this.fetchPostResponse(file);
        if (indices) {
            return {vertexData: new Float32Array(response), textureData: texture, specularMap: specularMap, indices: indices};
        }
        else {
            return {vertexData: new Float32Array(response), textureData: texture, specularMap: specularMap};
        }
    }

    async fetchPostResponse(file) {
        const response = await fetch(file).then(async function (response) {
            return response.text();
        });
        
        return this.objToArray(response);
    }
}

class JSCGL {
    // WebGPU attributes
    device;
    trianglePipeline;
    linePipeline;
    actualPipeline;

    // GPU Shaders
    cellShaderModule;
    depthTexture;

    // Canvas attributes
    canvasFormat;
    context;
    width;
    height;


    constructor(device, context, canvasFormat, width, height) {
        if (!navigator.gpu) {
            alert("WebGPU is not Supported on this browser");
            throw new Error("WebGPU is not Supported on this browser");
        }

        this.context = context;
        this.canvasFormat = canvasFormat;
        this.device = device;

        this.width = width;
        this.height = height;
    }

    async init() {

        this.cellShaderModule = this.device.createShaderModule({
            label: "My first WebGPU Shader",
            code: `
            struct VertexOut {
                @builtin(position) position: vec4f,
                @location(0) uvCoords: vec2f,
                @location(1) diffuse: vec3f,
                @location(2) specular: vec3f,
                @location(3) depth: f32,
                @location(4) shadowUv: vec2f
            }
            
            struct LineVertexOut {
                @builtin(position) position: vec4f
            }
            
            struct PointLight {
                position: vec3f,
                color: vec3f,
                diffuse: f32,
                specular: f32,
            }

            struct Line {
                a: vec3f,
                b: vec3f,
                color: vec4f,
                thickness: f32
            }

            @group(0) @binding(0) var sampler1: sampler;
            @group(0) @binding(1) var texture1: texture_2d<f32>;
            @group(0) @binding(2) var specularSampler: sampler;
            @group(0) @binding(3) var specularMap: texture_2d<f32>;
            
            @group(1) @binding(0) var<uniform> matWorld: mat4x4<f32>;
            @group(1) @binding(1) var<uniform> matFromEyeWorld: mat4x4<f32>;
            @group(1) @binding(2) var<uniform> matViewProj: mat4x4<f32>;
            @group(1) @binding(3) var<uniform> cameraView: vec3f;
            @group(1) @binding(4) var<uniform> lightPoint: array<PointLight, 8>;
            @group(1) @binding(5) var<uniform> numPointLights: u32;
            @group(1) @binding(6) var<uniform> invertNormals: f32;
            @group(1) @binding(7) var<uniform> viewport: vec2f;
            @group(1) @binding(8) var<uniform> matFromLightWorld: mat4x4<f32>;
            @group(1) @binding(9) var<uniform> matShadowProj: mat4x4<f32>;

            @group(2) @binding(0) var<uniform> line: Line;

            @group(3) @binding(0) var depthSample: sampler_comparison;
            @group(3) @binding(1) var depthTexture: texture_depth_2d;

            fn pointLightCalculation(light: PointLight, numPointLights: u32, finalNormal: vec3f, matWorld: mat3x3<f32>, vertWorldPosition: vec4f, cameraView: vec3f) -> array<vec3f, 2> {
                let lightPoint = vec3f(light.position[0], light.position[1], light.position[2]);
                let offset = lightPoint - vec3f(vertWorldPosition[0], vertWorldPosition[1], vertWorldPosition[2]);
                let distance = length(offset);
                let direction = normalize(offset);
                let specular = light.specular;
                let diffuse = light.diffuse;
                let attenuation = 1.0 / ( 0.0025 * distance * distance + 0.25);


                let diffuseRed = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[0] * diffuse;
                let diffuseGreen = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[1] * diffuse;
                let diffuseBlue = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[2] * diffuse;
                let specularRed = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[0] * specular;
                let specularGreen = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[1] * specular;
                let specularBlue = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[2] * specular;
                let diffuseLight = vec3f(diffuseRed * attenuation, diffuseGreen * attenuation, diffuseBlue * attenuation);
                let specularLight = vec3f(specularRed * attenuation, specularGreen * attenuation, specularBlue * attenuation);        
                
                return array(specularLight, diffuseLight);
            }
            
            fn directionalLightCalculation(light: PointLight, finalNormal: vec3f, matWorld: mat3x3<f32>, vertWorldPosition: vec4f, cameraView: vec3f) -> array<vec3f, 2> {
                let lightPoint = vec3f(light.position[0], light.position[1], light.position[2]);
                let offset = lightPoint - vec3f(vertWorldPosition[0], vertWorldPosition[1], vertWorldPosition[2]);
                let direction = normalize(lightPoint);
                let diffuse = light.diffuse;
                let specular = light.specular;

                let diffuseRed = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[0] * diffuse;
                let diffuseGreen = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[1] * diffuse;
                let diffuseBlue = max(0.0, dot(direction, normalize(matWorld * finalNormal))) * light.color[2] * diffuse;
                let specularRed = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[0] * specular;
                let specularGreen = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[1] * specular;
                let specularBlue = pow(max(0.0, dot(normalize(cameraView), normalize(reflect(-direction, normalize(matWorld * finalNormal))) ) ), 16 ) * light.color[2] * specular;
                
                let specularLight = vec3f(specularRed, specularGreen, specularBlue);
                let diffuseLight = vec3f(diffuseRed, diffuseGreen, diffuseBlue);

                return array(specularLight, diffuseLight);
            }
    
            @vertex
            fn vertexShader1(@location(0) vertPosition: vec3f, @location(1) uvCoords: vec2f, @location(2) normal: vec3f) -> VertexOut {
                var out: VertexOut;
                let finalNormal = vec3f(normal[0] * invertNormals, normal[1] * invertNormals, normal[2] * invertNormals);
                let vertWorldPosition = matWorld * vec4f(vertPosition, 1.0);

                let directionalLight = PointLight(vec3f(0, 1, 0), vec3f(1, 1, 1), 0.5, 0.5);

                out.diffuse = vec3f(0, 0, 0);
                out.specular = vec3f(0, 0, 0);
                let normalMatrix: mat3x3<f32> = mat3x3<f32>(
                    matWorld[0].xyz,
                    matWorld[1].xyz,
                    matWorld[2].xyz
                );

                for (var i: u32 = 0; i < numPointLights; i += 1) {
                    var calculation = pointLightCalculation(lightPoint[i], numPointLights, finalNormal, normalMatrix, vertWorldPosition, cameraView);
                    out.diffuse += calculation[1];
                    out.specular += calculation[0];
                }
                
                var calculation = directionalLightCalculation(directionalLight, finalNormal, normalMatrix, vertWorldPosition, cameraView);
                out.diffuse += calculation[1];
                out.specular += calculation[0];
                
                out.position = matViewProj * matFromEyeWorld * vec4f(vertPosition, 1);
                out.uvCoords = uvCoords;

                var shadowClipSpace = matShadowProj * matFromLightWorld * vec4f(vertPosition, 1);
                shadowClipSpace.z = 1.0 - shadowClipSpace.z;
                let shadowNdc = shadowClipSpace.xyz / shadowClipSpace.w;
                out.depth = 0.5 * shadowNdc.z + 0.5;
                out.shadowUv = vec2f(0.5, -0.5) * shadowNdc.xy + vec2f(0.5, 0.5); 
                return out;
            }

            @vertex
            fn lineVertexShader(@builtin(vertex_index) vertex_index: u32) -> LineVertexOut {
                var vertPosition: vec4f;
                var out: LineVertexOut;
                let a = line.a;
                let b = line.b;
                let clipA = matViewProj * matFromEyeWorld * vec4f(a, 1);
                let clipB = matViewProj * matFromEyeWorld * vec4f(b, 1);
                // Consigue los vertices que luego se renderizan en la pantalla (NDC)
                let ndcA = clipA.xy / clipA.w;
                let ndcB = clipB.xy / clipB.w;
                let lineDirection = normalize(ndcB - ndcA);
                var right = normalize(vec2f(-lineDirection.y, lineDirection.x));
                let offset = right * line.thickness * 0.5 / viewport;
                
                switch vertex_index {
                    case 0u: {vertPosition = vec4f((ndcA - offset) * clipA.w , clipA.z, clipA.w); break;}
                    case 1u: {vertPosition = vec4f((ndcA + offset) * clipA.w , clipA.z, clipA.w); break;}
                    case 2u: {vertPosition = vec4f((ndcB - offset) * clipB.w , clipB.z, clipB.w); break;}
                    case 3u: {vertPosition = vec4f((ndcA + offset) * clipA.w , clipA.z, clipA.w); break;}
                    case 4u: {vertPosition = vec4f((ndcB + offset) * clipB.w , clipB.z, clipB.w); break;}
                    case 5u: {vertPosition = vec4f((ndcB - offset) * clipB.w , clipB.z, clipB.w); break;}
                    default: {vertPosition = vec4f(0, 0, 0, 1);}
                }
                
                out.position = vertPosition;
                return out;
            }
            
            @vertex
            fn depthVertex(@location(0) vertPosition: vec3f) -> @builtin(position) vec4f {
                var clipSpace = matViewProj * matFromEyeWorld * vec4f(vertPosition, 1);
                return clipSpace;
            }

            @fragment
            fn lineFragmentShader() -> @location(0) vec4f {
                return line.color;
            }
    
            @fragment
            fn fragmentShader1(@location(0) uvCoords: vec2f, @location(1) diffuse: vec3f, @location(2) specular: vec3f, @location(3) depth: f32, @location(4) shadowUv: vec2f) -> @location(0) vec4f {
                var visibility: f32;
                let textureColor = textureSample(texture1, sampler1, uvCoords);
                let specularMap = textureSample(specularMap, specularSampler, uvCoords);

                visibility = textureSampleCompare(depthTexture, depthSample, shadowUv, depth);
                let texSize = vec2<f32>(textureDimensions(depthTexture));
                let uv = shadowUv;
                let texelCoords = vec2<i32>(uv * texSize);
                var rawDepth = textureLoad(depthTexture, texelCoords, 0);
                
                let textureRed = textureColor.r;
                let textureGreen = textureColor.g;
                let textureBlue = textureColor.b;

                let red = (textureRed * 0.6) + (textureRed * (diffuse[0] + (specular[0] * specularMap.r)));
                let green = (textureGreen * 0.6) + (textureGreen * (diffuse[1] + (specular[1] * specularMap.g)));
                let blue = (textureBlue * 0.6) + (textureBlue * (diffuse[2] + (specular[2] * specularMap.b)));
                return vec4f(red, green, blue, textureColor.a);
            }
    
            `
        });

        this.depthTexture = this.device.createTexture({
            size: [this.width, this.height, 1],
            format: "depth32float",
            usage: GPUTextureUsage.RENDER_ATTACHMENT | GPUTextureUsage.TEXTURE_BINDING |GPUTextureUsage.COPY_DST 
        });

        this.shadowMap = this.device.createTexture({
            size: [this.width, this.height, 1],
            format: "depth32float",
            usage: GPUTextureUsage.RENDER_ATTACHMENT | GPUTextureUsage.TEXTURE_BINDING
        });

        let positionBufferLayout = {
            arrayStride: 32,
            attributes: [{
                format: "float32x3",
                offset: 0,
                shaderLocation: 0
            }]
        }
        let uvCoordsBufferLayout = {
            arrayStride: 32,
            attributes: [{
                format: "float32x2",
                offset: 12,
                shaderLocation: 1
            }]
        }
        let normalsBufferLayout = {
            arrayStride: 32,
            attributes: [{
                format: "float32x3",
                offset: 20,
                shaderLocation: 2
            }]
        }

        const emptyLayout = this.device.createBindGroupLayout({label: "Empty Layout", entries: []});
        const lineGroupLayout = this.device.createBindGroupLayout({label: "Line Layout", entries: [
            {binding: 0, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}}
        ]});
        const matGroupLayout = this.device.createBindGroupLayout({label: "Mat Layout", entries: [
            {binding: 0, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 1, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 2, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 3, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 4, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 5, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 6, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 8, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 9, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}}

        ]});
        const lmatGroupLayout = this.device.createBindGroupLayout({label: "Line Mat Layout", entries: [
            {binding: 1, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 2, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 7, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}}
        ]});
        const dmatGroupLayout = this.device.createBindGroupLayout({label: "Depth Mat Layout", entries: [
            {binding: 1, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
            {binding: 2, visibility: GPUShaderStage.VERTEX | GPUShaderStage.FRAGMENT, buffer: {type: "uniform"}},
        ]});
        const texGroupLayout = this.device.createBindGroupLayout({label: "Tex Layout", entries: [
            {binding: 0, visibility: GPUShaderStage.FRAGMENT, sampler: {type: "filtering"}},
            {binding: 1, visibility: GPUShaderStage.FRAGMENT, texture: {sampleType: "float", viewDimension: "2d", multisampled: false}},
            {binding: 2, visibility: GPUShaderStage.FRAGMENT, sampler: {type: "filtering"}},
            {binding: 3, visibility: GPUShaderStage.FRAGMENT, texture: {sampleType: "float", viewDimension: "2d", multisampled: false}}
        ]});

        const depthTexLayout = this.device.createBindGroupLayout({label: "Depth Texture Layout", entries: [
            {binding: 0, visibility: GPUShaderStage.FRAGMENT, sampler: {type: "comparison"}},
            {binding: 1, visibility: GPUShaderStage.FRAGMENT, texture: {sampleType: "depth", viewDimension: "2d", multisampled: false}}
        ]});

        const linePipelineLayout = this.device.createPipelineLayout({
            bindGroupLayouts: [texGroupLayout, lmatGroupLayout, lineGroupLayout]
        });

        const trianglePipelineLayout = this.device.createPipelineLayout({
            bindGroupLayouts: [texGroupLayout, matGroupLayout, emptyLayout, depthTexLayout]
        });

        const depthPipelineLayout = this.device.createPipelineLayout({
            bindGroupLayouts: [emptyLayout, dmatGroupLayout]
        });

        this.depthPipeline = this.device.createRenderPipeline({
            label: "Depth Pipeline",
            layout: depthPipelineLayout,
            vertex: {
                module: this.cellShaderModule,
                entryPoint: "depthVertex",
                buffers: [positionBufferLayout]
            },
            depthStencil: {
                format: "depth32float",
                depthWriteEnabled: true,
                depthCompare: "less"
            }
        });

        this.trianglePipeline = this.device.createRenderPipeline({
            label: "Triangle Pipeline",
            layout: trianglePipelineLayout,
            vertex: {
                module: this.cellShaderModule,
                entryPoint: "vertexShader1",
                buffers: [positionBufferLayout, uvCoordsBufferLayout, normalsBufferLayout]
            },
            fragment: {
                module: this.cellShaderModule,
                entryPoint: "fragmentShader1",
                targets: [{
                    format: this.canvasFormat,
                        blend: {
                        color: {
                            srcFactor: 'src-alpha',
                            dstFactor: 'one-minus-src-alpha',
                            operation: 'add'
                        },
                        alpha: {
                            srcFactor: 'one',
                            dstFactor: 'zero',
                            operation: 'add'
                        }
                        },
                        writeMask: GPUColorWrite.ALL
                }]
            },
            depthStencil: {
                format: "depth32float",
                depthWriteEnabled: true,
                depthCompare: "less"
            }
        });

        this.linePipeline = this.device.createRenderPipeline({
            label: "Line Pipeline",
            layout: linePipelineLayout,
            vertex: {
                module: this.cellShaderModule,
                entryPoint: "lineVertexShader",
            },
            fragment: {
                module: this.cellShaderModule,
                entryPoint: "lineFragmentShader",
                targets: [{
                    format: this.canvasFormat
                }]
            },
            depthStencil: {
                format: "depth32float",
                depthWriteEnabled: true,
                depthCompare: "less"
            }
        });

    }

    setPipeline(option) {
        if (option == "triangle") {
            this.actualPipeline = this.trianglePipeline;
        } else if (option == "line") {
            this.actualPipeline = this.linePipeline;
        }
        else if (option == "depth") {
            this.actualPipeline = this.depthPipeline;
        }
    }

    createVertexBuffer(data) {
        let vertexBuffer = this.device.createBuffer({
            size: data.byteLength,
            usage: GPUBufferUsage.VERTEX | GPUBufferUsage.COPY_DST
        });

        this.device.queue.writeBuffer(vertexBuffer, 0, data);

        return vertexBuffer;
    }

    loadModel(model) {
        const textureBuffer = this.createTexture(model.texture);
        let specularMap;
        if (model.specularMap) {
            specularMap = this.createTexture(model.specularMap);
        } else {
            specularMap = this.createDefaultSpecularMap();
        }            
        const textureBindGroup = this.createTextureBindGroup(textureBuffer, specularMap);
        const vertexBuffer = this.createVertexBuffer(model.vertices);

        return {vertexBuffer: vertexBuffer, textureBindGroup: textureBindGroup, indices: model.indices, vertices: model.vertices.length/8};
    }

    newObject({id: id, model: model, position: shapePosition, size: shapeSize, velocity: shapeVelocity, rotation: shapeRotation, inverted: inverted, topology: topology}) {
        let position = [0, 0, 0];
        let velocity = [0, 0, 0];
        let size = [1, 1, 1];
        let rotation = [0, 0, 0];

        return new Object3D(id, shapePosition || position, shapeVelocity || velocity, shapeSize || size, shapeRotation || rotation, model, inverted);
    }

    newLine({id: id, type: type, start: lineStart, end: lineEnd, thickness: lineThickness, color: lineColor}) {
        let end = [0, 0, 0];
        let start = [0, 0, 0];
        let color = [1, 1, 1, 1];
        let thickness = 1;

        return new Line3D({id: id, type: type, start: lineStart || start, end: lineEnd || end, thickness: lineThickness || thickness, color: lineColor || color});
    }

    createUniformBuffer(data) {
        let size;
        if (data.byteLength < 16) {
            size = 16;
        }
        else {
            size = data.byteLength;
        }
        let buffer = this.device.createBuffer({
            size: size,
            usage: GPUBufferUsage.UNIFORM | GPUBufferUsage.COPY_DST
        });
        
        this.device.queue.writeBuffer(buffer, 0, data);

        return buffer;
    }

    createUniformBindGroup(group, bindings) {
        let entries = [];
        for (let i = 0; i < bindings.length; i++) {
            entries.push({ binding: bindings[i][1], resource: {buffer: bindings[i][0]} });  
        }
        let bindGroup = this.device.createBindGroup({
            layout: this.actualPipeline.getBindGroupLayout(group),
            entries: entries
        });

        return {group: group, bindGroup: bindGroup};
    }    

    createTexture(data, linear) {
        if (typeof linear == "undefined" || typeof linear == "null") {
            linear = true;
        }

        let sampler;
        let texture = this.device.createTexture({
            size: [data.width, data.height],
            format: "rgba8unorm",
            usage: GPUTextureUsage.TEXTURE_BINDING | GPUTextureUsage.COPY_DST | GPUTextureUsage.RENDER_ATTACHMENT
        });
        if (linear) {
            sampler = this.device.createSampler({
                minFilter: "linear",
                magFilter: "linear"
            });
        }
        else {
            sampler = this.device.createSampler();
        }

        this.device.queue.copyExternalImageToTexture(
            { source: data },
            { texture: texture },
            [data.width, data.height]
        );

        return {texture: texture, sampler: sampler};
    }

    createDefaultSpecularMap(linear) {
        if (typeof linear == "undefined" || typeof linear == "null") {
            linear = true;
        }

        let sampler;
        let textureSize = { width: 1, height: 1 };
        let blackColor = new Uint8Array([0, 0, 0, 255]);

        let texture = this.device.createTexture({
            size: textureSize,
            format: 'rgba8unorm',
            usage: GPUTextureUsage.COPY_DST | GPUTextureUsage.TEXTURE_BINDING
        });

        this.device.queue.writeTexture(
            { texture: texture },
            blackColor,
            { bytesPerRow: 4 },
            textureSize
        );

        if (linear) {
            sampler = this.device.createSampler({
                minFilter: "linear",
                magFilter: "linear"
            });
        }
        else {
            sampler = this.device.createSampler();
        }

        return { texture: texture, sampler: sampler };
    }

    createTextureBindGroup(texture, specularMap, id) {

        // Change If necessary
        if (typeof id == "undefined" || typeof id == "null") {
            id = 0;
        }

        let bindGroup = this.device.createBindGroup({
            layout: this.actualPipeline.getBindGroupLayout(id),
            entries: [
              { binding: 0, resource: texture.sampler },
              { binding: 1, resource: texture.texture.createView() },
              { binding: 2, resource: specularMap.sampler },
              { binding: 3, resource: specularMap.texture.createView() }
            ]
        });

        return {group: id, bindGroup: bindGroup};
    }
}