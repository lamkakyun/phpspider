## 有趣的东西
- zend公司维护php，因此php语言有很多zend函数

### config.m4
[关于config.m4](http://php.net/manual/zh/internals2.buildsys.configunix.php)
- 扩展的 config.m4 文件告诉 UNIX 构建系统哪些扩展 configure 选项是支持的，你需要哪些扩展库，以及哪些源文件要编译成它的一部分(自己下载一个php扩展看看就知道)
- config.m4 文件使用 GNU autoconf 语法编写
- PHP_ARG_*: 赋予用户可选项
- [autoconf 文档](http://www.gnu.org/software/autoconf/manual/autoconf.html)