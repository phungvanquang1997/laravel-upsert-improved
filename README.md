Hi guys, 

Phần repo này mình muốn chia sẽ một chút về phần cải thiện performance cho tính năng "upsert" của laravel - đã có từ phiên bản laravel v8.

Về cách upsert của laravel thực thi, thì họ đang dùng câu SQL: 

`
INSERT INTO ... ON DUPLICATE KEY UPDATE ...
`

đại loại sẽ gọi câu insert, nếu trùng key (khóa chính hoặc cặp khóa chính) sẽ bắt event "DUPLICATE KEY" rồi update record - thay vì thường phải viết 1 câu insert và update, thì dùng câu này để gộp cho gọn

**Vậy vấn đề gặp phải là gì ?

- Giả sử bạn upsert với lượng lớn data, 10k record chẳng hạn, thì sẽ có 10k query => vậy không ổn tí nào nhỉ?

**Giải pháp (tạm thời)

- Thay vì phải tạo ra 10k query, mình sẽ cố gắng giảm bớt nó thành 2 câu query (nhưng không hẳn sẽ nhanh hơn trong trường hợp update)
  + 1 câu dùng batch insert: logic là sẽ tìm những id không có trong db rồi gộp chúng lại, phần còn lại là để update
  
  + 1 câu dùng batch update: update...case...when

** Vậy upsert của laravel dùng ổn trong trường hợp nào?

- Khi bạn nghĩ luồng logic của bạn đang làm có ít dữ liệu (chỉ có 5-10 records) thì dùng có sẵn của laravel cho nhanh, cũng không đáng kể
- và câu upsert (SQL trên) có hổ trợ nhiều key (khóa chính và cặp khóa chính) 

** Một vài lưu ý
- Hiện tại code của mình chỉ support 1 field thôi nhé :D
- Bạn có thể chỉ dùng trait SqlBulkUpdatable nếu bạn chỉ muốn dùng cho bulk update, hoặc wantsUpsertQuery cho cả update và insert
- Có thể trong Model bạn phải thêm hàm để format lại updated_at và created_at

  protected function serializeDate(\DateTimeInterface $date)
  {
      return $date->format('Y-m-d H:i:s');
  }


Thanks for reading, happy coding
