![AES 加密](http://images.cnblogs.com/cnblogs_com/happyhippy/AES1.jpg)

[AES五种加密模式（CBC、ECB、CTR、OCF、CFB）](http://www.cnblogs.com/starwolf/p/3365834.html)

[块密码的工作模式](https://zh.wikipedia.org/wiki/%E5%9D%97%E5%AF%86%E7%A0%81%E7%9A%84%E5%B7%A5%E4%BD%9C%E6%A8%A1%E5%BC%8F#.E5.88.9D.E5.A7.8B.E5.8C.96.E5.90.91.E9.87.8F.EF.BC.88IV.EF.BC.89)

```
ECB模式不需要IV的
如果是非ECB模式的话 解密也需要用到IV的
```

> 如果使用 CFB 和 OFB 模式， 必须提供初始向量（IV）， 如果使用 CBC 模式， 可以提供一个初始向量。 初始向量必须是唯一的，并且在加密和解密过程中要保持一致。 你可以将初始向量和加密后数据一起存储， 其存储位置可以由一个函数的输出来指定， 例如文件名的 MD5 散列值， 这样你就可以把初始向量和加密后的数据一起传输 

[Stackoverflow:OpenSSL or Mcrypt?](http://stackoverflow.com/questions/36571743/openssl-or-mcrypt-openssl-encrypt-or-mcrypt-encrypt)