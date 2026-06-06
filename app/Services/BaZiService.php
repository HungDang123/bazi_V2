<?php

namespace App\Services;

use DateTime;
use Overtrue\ChineseCalendar\Calendar;

class BaZiService

{
    protected static $stems = ['Giáp', 'Ất', 'Bính', 'Đinh', 'Mậu', 'Kỷ', 'Canh', 'Tân', 'Nhâm', 'Quý'];
    protected static $branches = ['Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi'];
    protected static $elements = ['Mộc', 'Mộc', 'Hỏa', 'Hỏa', 'Thổ', 'Thổ', 'Kim', 'Kim', 'Thủy', 'Thủy'];
    protected static $yinYang   = ['Dương', 'Âm', 'Dương', 'Âm', 'Dương', 'Âm', 'Dương', 'Âm', 'Dương', 'Âm'];
    protected static $arrNC = [0, 0, 0, 0, 0, 0, 0, 0];

    protected static $giaiphapdungthan = [
        'Kim' => [
            'tinh_chat_ngu_hanh' => 'Hành Kim đại diện cho sự cứng rắn, sắc bén, quy củ và chính trực. Nó tượng trưng cho kim loại, khí lạnh, ánh sáng thanh khiết, khả năng cắt gọt và phân tích. Người có Dụng Thần là Kim thường cần tăng tính nguyên tắc, tư duy sắc sảo, khả năng hành động dứt khoát và hướng tới tổ chức chặt chẽ. Tăng Kim giúp ổn định tâm trí, nâng cao khả năng quyết đoán, khả năng lập kế hoạch chiến lược và kỹ thuật chi tiết.',
            'nganh_nghe_phu_hop' => [
                ['key' => 'Pháp Luật', 'value' => 'Luật sư, kiểm sát viên, thẩm phán'],
                ['key' => 'Kỹ thuật – Tài chính', 'value' => 'Kế toán, kiểm toán, ngân hàng, chứng khoán'],
                ['key' => 'Công nghiệp – Cơ khí', 'value' => 'Kỹ sư cơ điện tử, gia công kim loại'],
                ['key' => 'Hành chính – Tổ chức', 'value' => 'Quản trị doanh nghiệp, hành chính, nhân sự'],
                ['key' => 'Bảo hiểm – Phân tích', 'value' => 'Tư vấn tài chính, bảo hiểm, phân tích dữ liệu'],
                ['key' => 'Vàng bạc – Đá quý', 'value' => 'Kinh doanh vàng bạc, kim hoàn, trang sức'],
                ['key' => 'Luyện kim – Khoáng sản', 'value' => 'Khai thác khoáng sản, sản xuất kim loại'],
            ],
            'mau_sac_nen_dung' => [
                ['key' => 'Trắng, bạc, ánh kim', 'value' => 'Tượng trưng cho khí chất thanh khiết'],
                ['key' => 'Vàng ánh kim, bạch kim', 'value' => 'Thể hiện giá trị vật chất và sang trọng'],
            ],
            'trang_suc_vat_pham' => [
                'Sim phong thuỷ',
                'Số tài khoản phong thuỷ',
                '●	Vật phẩm cải vận 1
                    ○	Vòng tay Bạch Ngọc trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Bạch Ngọc trong Bộ Sưu Tập Vương Chiêu Bảo
                    =>	Kích Hoạt năng lượng dụng thần hành Kim của bạn bằng Bạch Ngọc Trắng. Loại ngọc mang năng lượng tinh khiết nhất trong dòng ngọc, tượng trưng cho ánh sáng, trí tuệ và sự bình an thuần khiết. Bạch Ngọc có khả năng cộng hưởng với trường khí hành Kim, giúp làm sáng tâm trí, và khai mở năng lượng “tinh khiết – chính trực – vững vàng”
                    =>	Kích hoạt năng lượng của năng lượng thịnh vượng và tiền bạc hanh thông nhờ biểu tượng CHARM THIỀM THỪ, linh vật chiêu tài hàng đầu trong phong thuỷ Á Đông. Giúp chủ nhân chiêu tài – tụ bảo – tránh hao tài tán lộc.
                    Trong phong thuỷ, năng lượng tài khí không tự nhiên mà có, nó cần được “dẫn đường và kích hoạt”. Nếu không bản thân sẽ dễ gặp thất thoát tài chính, tiền vào ít, ra nhiều. 
                    Khi năng lượng tài lộc được kích hoạt đúng cách, bạn sẽ:
                    -	Thu hút cơ hội kinh doanh, đối tác và khách hàng
                    -	Giữ được tiền của, tránh hao tổn
                    -	Tăng tần số thịnh vượng trong tư duy và quyết định',
                '●	Vật Phẩm cải vận 2:
                    ○	Vòng tay Bạch Ngọc trong Bộ Sưu Tập Vương Kim Quy
                    ○	Vòng chuỗi Bạch Ngọc trong Bộ Sưu Tập Vương Kim Quy 
                    =>	Kích Hoạt năng lượng dụng thần hành Kim của bạn bằng Bạch Ngọc Trắng. Loại ngọc mang năng lượng tinh khiết nhất trong dòng ngọc, tượng trưng cho ánh sáng, trí tuệ và sự bình an thuần khiết. Bạch Ngọc có khả năng cộng hưởng với trường khí hành Kim, giúp làm sáng tâm trí, và khai mở năng lượng “tinh khiết – chính trực – vững vàng
                    =>	Kích Hoạt năng lượng Trường Thọ nhờ biểu tượng Charm THẦN KIM QUY.
                    Trong Ngũ Hành, thân thể con người là một tiểu vũ trụ. Khi năng lượng bị mất cân bằng, cơ thể sẽ suy yếu, tinh thần giảm sút, trí tuệ mờ mịt. 
                    Việc kích hoạt năng lượng Trường Thọ giúp:
                    -	Tăng sinh khí, hỗ trợ sức khoẻ toàn thân
                    -	Giữ tâm an định, gỉam căng thẳng
                    -	Củng cố năng lượng DƯƠNG, giúp tinh thần tỉnh táo và sáng suốt
                    -	Chảm Kim Quy như 1 vật bảo hộ cho trường khí của chủ nhân, gíup tránh năng lượng xấu xâm nhập.
                ',
                '●	Vật Phẩm cải vận 3:
                    ○	Vòng tay Bạch Ngọc trong Bộ Sưu Tập Vương Bách Duyên
                    ○	Vòng tay Bạch Ngọc trong Bộ Sưu Tập Vương Bách Duyên
                    =>	Kích Hoạt năng lượng dụng thần hành Kim của bạn bằng Bạch Ngọc Trắng. Loại ngọc mang năng lượng tinh khiết nhất trong dòng ngọc, tượng trưng cho ánh sáng, trí tuệ và sự bình an thuần khiết. Bạch Ngọc có khả năng cộng hưởng với trường khí hành Kim, giúp làm sáng tâm trí, và khai mở năng lượng “tinh khiết – chính trực – vững vàng
                    =>	Kích Hoạt năng lượng tình duyên, thu hút tình cảm, cải thiện mối quan hệ hoặc mở rộng kết nối nhờ Charm Trứng Bách Duyên
                    Trong phong thuỷ, Nhân Duyên và Mối Quan Hệ là phần năng lượng mềm nhưng có sức ảnh hưởng mạnh nhất đến vận trình của mỗi người.
                    Một ngừoi có tài, có trí mà không có duyên sẽ thường gặp khó khăn mà không ai giúp đỡ, cô đơn hoặc lỡ mất những cơ hội quan trọng.
                    Khi năng lượng tình duyên được kích hoạt:
                    -	Tình yêu viên mãn, mối quan hệ hoà hợp, gia đạo yên vui.
                    -	Công việc thuận lợi hơn nhờ quý nhân giúp đỡ, đối tác tin tưởng
                    -	Tâm hồn nhẹ nhàng, thu hút, rạng rỡ - năng lượng toả ra khiến người khác dễ mến

                '
            ],
            'hop_tuoi_ket_hop_lam' => [
                'Hợp tác nên ưu tiên tuổi Tỵ, Dậu Sửu',
                'Thời gian sinh Kim vượng:	
                    ●	8/8 – 7/10 dương lịch
                    ●	Giờ 15h – 19h (Thân, Dậu)
                    ●	Bát Tự có tam hợp: Sửu – Dậu – Tỵ hoặc Thân – Dậu – Tuất 
                '
            ],
            'ban_than_hanh_vi' => [
                'Thời điểm may mắn trong năm: 8/8 – 7/10 dương lịch',
                'Thời điểm may mắn trong ngày:
                    ●	1h – 3h sáng (Sửu)

                    ●	5h – 7h sáng (Mão)

                    ●	15h – 21h tối (Thân – Dậu – Tuất) |

                    ●	Trang phục: Quần áo màu trắng, bạc, đệm chăn màu trắng

                    ●	Đeo vàng có đốm màu, bông tai vàng, nhẫn bạc

                    ●	Tăng cường soi gương mỗi ngày
                '
            ],
            'ket_luan' => 'Người có mệnh khuyết Kim nên tăng cường sử dụng các yếu tố thuộc Kim trong đời sống hàng ngày, bao gồm: màu sắc trắng bạc, vật liệu kim loại quý, tranh phong thủy, và duy trì sinh hoạt đúng khung giờ Kim cường.
Làm việc trong các ngành nghề có tổ chức rõ ràng, kỹ thuật cao, hoặc liên quan đến tài chính – cơ khí sẽ giúp thân mệnh phát huy tốt nhất.
Kết hợp làm ăn với người sinh vào giờ Kim vượng hoặc tuổi Thân – Dậu – Tỵ sẽ tăng vận khí.
'
        ],
        'Mộc' => [
            'tinh_chat_ngu_hanh' => 'Hành Mộc đại diện cho sự sinh trưởng, phát triển, sáng tạo và mềm dẻo. Mộc là yếu tố mang tính sống động, thiên về nhân đạo, trí tuệ, sự phát triển bền vững, có xu hướng giáo dục – nuôi dưỡng – bao dung.
Người có Dụng Thần là Mộc thường cần tăng cường năng lượng của sự dịu dàng, linh hoạt, khả năng thấu hiểu và phát triển dài hạn.
Tăng Mộc giúp mở rộng tư duy, cải thiện sức khỏe gan mật, tăng trưởng vận trình, phát triển học hành và sự nghiệp bền vững.
',
            'nganh_nghe_phu_hop' => [
                ['key' => 'Giáo dục – học thuật', 'value' => 'Giáo viên, giảng viên, nhà nghiên cứu, học giả'],
                ['key' => 'Nông – Lâm nghiệp', 'value' => 'Nông dân, lâm nghiệp, làm vườn, cảnh quan, cây cảnh'],
                ['key' => 'Y học cổ truyền', 'value' => 'Đông y, dược liệu, trị liệu tự nhiên'],
                ['key' => 'Nghệ thuật – sáng tạo', 'value' => 'Thiết kế thời trang, nội thất, mỹ thuật, mỹ nghệ'],
                ['key' => 'Văn hóa – báo chí', 'value' => 'Viết lách, biên tập, xuất bản, báo chí'],
                ['key' => 'Tư vấn – trị liệu', 'value' => 'Tâm lý trị liệu, huấn luyện viên phát triển cá nhân'],
                ['key' => 'Ngành liên quan gỗ', 'value' => 'Kinh doanh đồ gỗ, nghề mộc, trang trí nội thất']
            ],
            'mau_sac_nen_dung' => [
                ['key' => 'Xanh lá, xanh cốm, xanh rêu', 'value' => 'Trang phục, nội thất, phụ kiện'],
                ['key' => 'Màu gỗ tự nhiên, nâu nhạt', 'value' => 'Đồ nội thất, sàn nhà, bàn ghế'],
                ['key' => 'Xanh lục nhạt', 'value' => 'Rèm cửa, drap giường, vải phủ'],
            ],
            'trang_suc_vat_pham' => [
                'Sim phong thuỷ',
                'Số tài khoản phong thuỷ',
                '●	Vật phẩm cải vận 1
                    ○	Vòng tay Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Chiêu Bảo
                    =>	Kích Hoạt năng lượng dụng thần hành Mộc của bạn bằng Ngọc Cẩm Thạch, biểu tượng mạnh mẽ của hành Mộc nhờ sắc xanh tự nhiên, mang năng lượng của cây cối, sự sinh trưởng và thịnh vượng
                    =>	Kích hoạt năng lượng của năng lượng thịnh vượng và tiền bạc hanh thông nhờ biểu tượng Charm Thiềm Thừ, linh vật chiêu tài hàng đầu trong phong thuỷ Á Đông. Giúp chủ nhân chiêu tài – tụ bảo – tránh hao tài tán lộc.
                    Trong phong thuỷ, năng lượng tài khí không tự nhiên mà có, nó cần được “dẫn đường và kích hoạt”. Nếu không bản thân sẽ dễ gặp thất thoát tài chính, tiền vào ít, ra nhiều. 
                    Khi năng lượng tài lộc được kích hoạt đúng cách, bạn sẽ:
                    -	Thu hút cơ hội kinh doanh, đối tác và khách hàng
                    -	Giữ được tiền của, tránh hao tổn
                    -	Tăng tần số thịnh vượng trong tư duy và quyết định
                ',
                '●	Vật Phẩm cải vận 2:
                    ○	Vòng tay Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Kim Quy
                    ○	Vòng chuỗi Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Kim Quy 
                    =>	Kích Hoạt năng lượng dụng thần hành Mộc của bạn bằng Ngọc Cẩm Thạch, biểu tượng mạnh mẽ của hành Mộc nhờ sắc xanh tự nhiên, mang năng lượng của cây cối, sự sinh trưởng và thịnh vượng
                    =>	Kích Hoạt năng lượng Trường Thọ nhờ biểu tượng Charm THẦN KIM QUY.
                    Trong Ngũ Hành, thân thể con người là một tiểu vũ trụ. Khi năng lượng bị mất cân bằng, cơ thể sẽ suy yếu, tinh thần giảm sút, trí tuệ mờ mịt. 
                    Việc kích hoạt năng lượng Trường Thọ giúp:
                    -	Tăng sinh khí, hỗ trợ sức khoẻ toàn thân
                    -	Giữ tâm an định, gỉam căng thẳng
                    -	Củng cố năng lượng DƯƠNG, giúp tinh thần tỉnh táo và sáng suốt
                    -	Chảm Kim Quy như 1 vật bảo hộ cho trường khí của chủ nhân, gíup tránh năng lượng xấu xâm nhập.

                ',
                '●	Vật Phẩm cải vận 3:
                    ○	Vòng tay Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Bách Duyên

                    ○	Vòng tay Ngọc Cẩm Thạch trong Bộ Sưu Tập Vương Bách Duyên

                    =>	Kích Hoạt năng lượng dụng thần hành Mộc của bạn bằng Ngọc Cẩm Thạch, biểu tượng mạnh mẽ của hành Mộc nhờ sắc xanh tự nhiên, mang năng lượng của cây cối, sự sinh trưởng và thịnh vượng
                    =>	Kích Hoạt năng lượng tình duyên, thu hút tình cảm, cải thiện mối quan hệ hoặc mở rộng kết nối nhờ Charm Trứng Bách Duyên
                    Trong phong thuỷ, Nhân Duyên và Mối Quan Hệ là phần năng lượng mềm nhưng có sức ảnh hưởng mạnh nhất đến vận trình của mỗi người.
                    Một ngừoi có tài, có trí mà không có duyên sẽ thường gặp khó khăn mà không ai giúp đỡ, cô đơn hoặc lỡ mất những cơ hội quan trọng.
                    Khi năng lượng tình duyên được kích hoạt:
                    -	Tình yêu viên mãn, mối quan hệ hoà hợp, gia đạo yên vui.
                    -	Công việc thuận lợi hơn nhờ quý nhân giúp đỡ, đối tác tin tưởng
                    -	Tâm hồn nhẹ nhàng, thu hút, rạng rỡ - năng lượng toả ra khiến người khác dễ mến

                '

            ],
            'hop_tuoi_ket_hop_lam' => [
                'Tam hợp – Tam hội: Hợi – Mão – Mùi / Dần – Mão – Thìn',
                'Lục hợp: Dần – Hợi',
                'Thời điểm vượng Mộc:	
                    ●	Trong năm: 19/2 – 5/4 dương lịch

                    ●	Trong ngày: 3h – 7h sáng

                    ●	Hướng Đông – Đông Nam

                    ●	Quốc gia vượng Mộc: Nhật Bản, Mỹ

                    ●	Thành phố: Tokyo (Đông Kinh) 
                '
            ],
            'ban_than_hanh_vi' => [
                'Để tóc và móng tay dài (nam để móng tay út dài 5mm)',
                'Không nên cạo râu sát, tránh dao cạo (vì hành kim nhiều mà KIM khắc MỘC). Nên dùng máy cạo râu.',
                'Trang phục xanh lá, vải mềm, dễ co giãn',
                'Ngủ trên giường gỗ, chăn đệm màu xanh',
                'Đeo dây chuyền hình lá, dùng bút gỗ',
                'Mang theo tràng hạt gỗ, vật phẩm từ cây',
                'Mỗi tối trước khi ngủ là thời điểm tốt nhất của Mộc'

            ],
            'ket_luan' => 'Người có mệnh cần Mộc nên tăng cường các yếu tố xanh tự nhiên trong đời sống như cây xanh, đồ gỗ, đồ thủ công, thực phẩm rau quả, để giúp vận trình phát triển ổn định, sự nghiệp thăng tiến bền vững.
Công việc liên quan đến giáo dục, sáng tạo, y học cổ truyền, hoặc môi trường tự nhiên sẽ mang lại thuận lợi rõ rệt.
Kết hợp với tuổi Mão, Dần, Hợi, Thìn là có lợi. Nên sinh hoạt, học tập và làm việc trong khoảng giờ – mùa Mộc vượng để nâng cao năng lượng thân mệnh.
'
        ],
        'Thủy' => [
            'tinh_chat_ngu_hanh' => 'Thủy tượng trưng cho trí tuệ, lưu thông, mềm mại, linh hoạt, đồng thời cũng đại diện cho sự kết nối, giao tiếp và thích nghi. Người có Dụng Thần là Thủy thường cần tăng cường năng lượng của sự thấu hiểu, biến hóa và chuyển động để cải thiện vận trình.
Tăng Thủy giúp ổn định tâm trí, mở rộng quan hệ, tăng khả năng học hỏi và linh hoạt trong các tình huống cuộc sống.
',
            'nganh_nghe_phu_hop' => [
                ['key' => 'Truyền thông – giao tiếp', 'value' => 'MC, nhà báo, truyền thông, giảng viên'],
                ['key' => 'Vận tải – giao thương', 'value' => 'Logistics, xuất nhập khẩu, tài xế'],
                ['key' => 'Thủy sản – biển – nước', 'value' => 'Nuôi trồng thủy sản, tàu biển, cứu hộ'],
                ['key' => 'Y tế – trị liệu', 'value' => 'Nha sĩ, bác sĩ, điều dưỡng, tâm lý trị liệu'],
                ['key' => 'Môi trường – khí tượng', 'value' => 'Khí tượng thủy văn, xử lý nước, môi trường biển'],
                ['key' => 'Tài chính – bảo hiểm', 'value' => 'Kế toán, ngân hàng, bảo hiểm, chứng khoán'],
                ['key' => 'Công nghệ – phân tích', 'value' => 'Kỹ sư phần mềm, phân tích dữ liệu']
            ],
            'mau_sac_nen_dung' => [
                ['key' => 'Đen, xanh biển đậm, xanh da trời', 'value' => 'Trang phục, phụ kiện, chăn ga'],
                ['key' => 'Trắng bạc (Kim sinh Thủy)', 'value' => 'Đồ dùng, vật phẩm phong thủy'],
                ['key' => 'Xanh nước biển, xanh ngọc', 'value' => 'Nội thất, màu xe, văn phòng phẩm'],
            ],
            'trang_suc_vat_pham' => [
                'Sim phong thuỷ',
                'Số tài khoản phong thuỷ',
                '●	Vật phẩm cải vận 1
                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Chiêu Bảo
                    	Kích Hoạt năng lượng dụng thần hành Thuỷ của bạn bằng Lam Ngọc Phỉ Thuý - một trong những viên đá mang trường năng lượng Thuỷ mạnh nhất.
                    Màu lam ngọc dịu sâu, trong trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích hoạt năng lượng của năng lượng thịnh vượng và tiền bạc hanh thông nhờ biểu tượng Charm Thiềm Thừ, linh vật chiêu tài hàng đầu trong phong thuỷ Á Đông. Giúp chủ nhân chiêu tài – tụ bảo – tránh hao tài tán lộc.
                    Trong phong thuỷ, năng lượng tài khí không tự nhiên mà có, nó cần được “dẫn đường và kích hoạt”. Nếu không bản thân sẽ dễ gặp thất thoát tài chính, tiền vào ít, ra nhiều. 
                    Khi năng lượng tài lộc được kích hoạt đúng cách, bạn sẽ:
                    -	Thu hút cơ hội kinh doanh, đối tác và khách hàng
                    -	Giữ được tiền của, tránh hao tổn
                    -	Tăng tần số thịnh vượng trong tư duy và quyết định

                ',
                '●	Vật Phẩm cải vận 2:
                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Kim Quy
                    ○	Vòng chuỗi Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Kim Quy 
                    	Kích Hoạt năng lượng dụng thần hành Thuỷ của bạn bằng Lam Ngọc Phỉ Thuý - một trong những viên đá mang trường năng lượng Thuỷ mạnh nhất.
                    Màu lam ngọc dịu sâu, trong trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng Trường Thọ nhờ biểu tượng Charm THẦN KIM QUY.
                    Trong Ngũ Hành, thân thể con người là một tiểu vũ trụ. Khi năng lượng bị mất cân bằng, cơ thể sẽ suy yếu, tinh thần giảm sút, trí tuệ mờ mịt. 
                    Việc kích hoạt năng lượng Trường Thọ giúp:
                    -	Tăng sinh khí, hỗ trợ sức khoẻ toàn thân
                    -	Giữ tâm an định, giảm căng thẳng
                    -	Củng cố năng lượng DƯƠNG, giúp tinh thần tỉnh táo và sáng suốt
                    -	Chảm Kim Quy như 1 vật bảo hộ cho trường khí của chủ nhân, giúp tránh năng lượng xấu xâm nhập.
                ',
                '●	Vật Phẩm cải vận 3:
                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Bách Duyên

                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Bách Duyên

                    	Kích Hoạt năng lượng dụng thần hành Thuỷ của bạn bằng Lam Ngọc Phỉ Thuý - một trong những viên đá mang trường năng lượng Thuỷ mạnh nhất.
                    Màu lam ngọc dịu sâu, trong trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng tình duyên, thu hút tình cảm, cải thiện mối quan hệ hoặc mở rộng kết nối nhờ Charm Trứng Bách Duyên
                    Trong phong thuỷ, Nhân Duyên và Mối Quan Hệ là phần năng lượng mềm nhưng có sức ảnh hưởng mạnh nhất đến vận trình của mỗi người.
                    Một ngừoi có tài, có trí mà không có duyên sẽ thường gặp khó khăn mà không ai giúp đỡ, cô đơn hoặc lỡ mất những cơ hội quan trọng.
                    Khi năng lượng tình duyên được kích hoạt:
                    -	Tình yêu viên mãn, mối quan hệ hoà hợp, gia đạo yên vui.
                    -	Công việc thuận lợi hơn nhờ quý nhân giúp đỡ, đối tác tin tưởng
                    -	Tâm hồn nhẹ nhàng, thu hút, rạng rỡ - năng lượng toả ra khiến người khác dễ mến
                '

            ],
            'hop_tuoi_ket_hop_lam' => [
                'Tam hợp – Tam hội: Thân – Tý – Thìn / Hợi – Mão – Mùi',
                'Lục hợp: Tý – Sửu',
                'Thời điểm vượng Thủy:	
                    ●	Trong năm: 8/11 – 6/1 dương lịch

                    ●	Trong ngày: 21h – 1h sáng

                    ●	Hướng Bắc

                    ●	Quốc gia vượng Thủy: Canada, Phần Lan, các nước Bắc Âu

                    ●	Thành phố: Thượng Hải (ven biển), Hong Kong
                '
            ],
            'ban_than_hanh_vi' => [
                'Tắm nước mát mỗi sáng hoặc xông hơi thường xuyên',
                'Đeo trang sức kim loại, đá xanh đen',
                'Uống nhiều nước, ăn nhiều đồ mát',
                'Hạn chế ăn cay nóng hoặc làm việc quá căng thẳng',
                'Nên học tập và làm việc về đêm (trong giới hạn cho phép)',
                'Trang phục thường ngày chọn màu xanh biển hoặc đen',

            ],
            'ket_luan' => 'Người có mệnh cần Thủy nên tăng cường các yếu tố về nước, kim loại, và sắc xanh dương – đen trong đời sống.
 Môi trường lý tưởng là nơi gần nước, ẩm mát, hoặc có yếu tố Thủy trong nhà.
Công việc mang tính kết nối, di chuyển, tài chính – phân tích là các hướng đi thuận lợi.
Giờ sinh hoạt và thời gian hoạt động buổi tối, mùa đông cũng giúp người mệnh Thủy tăng vận rõ rệt.
'
        ],
        'Hỏa' => [
            'tinh_chat_ngu_hanh' => 'Hỏa đại diện cho nhiệt huyết, ánh sáng, tinh thần, sự hưng phấn, khai mở và đột phá. Người có Dụng Thần là Hỏa thường có thiên hướng bộc lộ cảm xúc, có nhu cầu được công nhận, muốn tỏa sáng hoặc lãnh đạo. Khi mệnh thiếu Hỏa, dễ rơi vào trạng thái trì trệ, u uất, thiếu ý chí, mất định hướng và hay lo âu.
Bổ sung hành Hỏa giúp người mệnh Hỏa tăng dương khí, cải thiện sức khỏe tim mạch, tinh thần sáng rõ và tăng khả năng quyết đoán.
',
            'nganh_nghe_phu_hop' => [
                ['key' => 'Công nghệ – Kỹ thuật', 'value' => 'IT, điện tử, phần mềm, kỹ sư điện, kỹ sư năng lượng'],
                ['key' => 'Y học – Điều trị', 'value' => 'Bác sĩ, nha sĩ, điều dưỡng, y học hiện đại'],
                ['key' => 'Thẩm mỹ – Trang điểm', 'value' => 'Làm đẹp, spa, make-up artist'],
                ['key' => 'Ẩm thực – Nấu nướng', 'value' => 'Bếp trưởng, đầu bếp, chuyên gia ẩm thực'],
                ['key' => 'Giải trí – Sáng tạo', 'value' => 'Diễn viên, MC, đạo diễn, nhiếp ảnh gia'],
                ['key' => 'Lãnh đạo – Quản lý', 'value' => 'Điều hành doanh nghiệp, quản lý nhóm'],
                ['key' => 'Thể thao – Truyền động lực', 'value' => 'HLV thể thao, gym, huấn luyện viên kỹ năng mềm']
            ],
            'mau_sac_nen_dung' => [
                ['key' => 'Đỏ, cam, hồng đậm', 'value' => 'Áo, giày, phụ kiện, nội thất'],
                ['key' => 'Tím, vàng nhạt (Thổ sinh Hỏa)', 'value' => 'Màn rèm, chăn ga, đồ da'],
                ['key' => 'Nâu đỏ, hồng cam đất', 'value' => 'Son, túi xách, vải vóc'],
            ],
            'trang_suc_vat_pham' => [
                'Sim phong thuỷ',
                'Số tài khoản phong thuỷ',
                '●	Vật phẩm cải vận 1
                    ○	Vòng tay Ngọc Nam Hồng trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Thạch Anh Mặt Trời trong Bộ Sưu Tập Vương Chiêu Bảo
                    	Kích Hoạt năng lượng dụng thần Hành Hoả bằng Ngọc Nam Hồng và Thạch Anh Mặt Trời ( Sunstone ) 
                    -	Ngọc Nam Hồng được mệnh danh là “Ngọc của trái tim”, là viên đá chứa năng lượng Hỏa nhu – ấm áp, từ bi, và tinh tế.
                    Không bùng cháy dữ dội như Hỏa Dương, mà lan tỏa âm ấm, dịu nhẹ như ánh hoàng hôn, giúp con người phục hồi cảm xúc, tăng khả năng yêu thương, và kích hoạt năng lượng sống tích cực.
                    -	Thạch Anh Mặt Trời được mệnh danh là “Viên đá của Thái Dương”, mang năng lượng Hỏa mạnh mẽ nhất trong thế giới khoáng vật.
                    Ánh cam ánh vàng tựa mặt trời rực rỡ không chỉ tượng trưng cho nguồn sáng, mà còn là trường khí của sự sống, tự tin và thịnh vượng.

                    	Kích hoạt năng lượng của năng lượng thịnh vượng và tiền bạc hanh thông nhờ biểu tượng Charm Thiềm Thừ, linh vật chiêu tài hàng đầu trong phong thuỷ Á Đông. Giúp chủ nhân chiêu tài – tụ bảo – tránh hao tài tán lộc.
                    Trong phong thuỷ, năng lượng tài khí không tự nhiên mà có, nó cần được “dẫn đường và kích hoạt”. Nếu không bản thân sẽ dễ gặp thất thoát tài chính, tiền vào ít, ra nhiều. 
                    Khi năng lượng tài lộc được kích hoạt đúng cách, bạn sẽ:
                    -	Thu hút cơ hội kinh doanh, đối tác và khách hàng
                    -	Giữ được tiền của, tránh hao tổn
                    -	Tăng tần số thịnh vượng trong tư duy và quyết định

                ',
                '●	Vật Phẩm cải vận 2:
                    ○	Vòng tay Ngọc Nam Hồng trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Thạch Anh Mặt Trời trong Bộ Sưu Tập Vương Chiêu Bảo
                    	Kích Hoạt năng lượng dụng thần Hành Hoả bằng Ngọc Nam Hồng và Thạch Anh Mặt Trời ( Sunstone ) 
                    -	Ngọc Nam Hồng được mệnh danh là “Ngọc của trái tim”, là viên đá chứa năng lượng Hỏa nhu – ấm áp, từ bi, và tinh tế.
                    Không bùng cháy dữ dội như Hỏa Dương, mà lan tỏa âm ấm, dịu nhẹ như ánh hoàng hôn, giúp con người phục hồi cảm xúc, tăng khả năng yêu thương, và kích hoạt năng lượng sống tích cực.
                    -	Thạch Anh Mặt Trời được mệnh danh là “Viên đá của Thái Dương”, mang năng lượng Hỏa mạnh mẽ nhất trong thế giới khoáng vật.
                    Ánh cam ánh vàng tựa mặt trời rực rỡ không chỉ tượng trưng cho nguồn sáng, mà còn là trường khí của sự sống, tự tin và thịnh vượng.như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng Trường Thọ nhờ biểu tượng Charm THẦN KIM QUY.
                    Trong Ngũ Hành, thân thể con người là một tiểu vũ trụ. Khi năng lượng bị mất cân bằng, cơ thể sẽ suy yếu, tinh thần giảm sút, trí tuệ mờ mịt. 
                    Việc kích hoạt năng lượng Trường Thọ giúp:
                    -	Tăng sinh khí, hỗ trợ sức khoẻ toàn thân
                    -	Giữ tâm an định, giảm căng thẳng
                    -	Củng cố năng lượng DƯƠNG, giúp tinh thần tỉnh táo và sáng suốt
                    -	Chảm Kim Quy như 1 vật bảo hộ cho trường khí của chủ nhân, giúp tránh năng lượng xấu xâm nhập.
                ',
                '●	Vật Phẩm cải vận 3:
                    ○	Vòng tay Ngọc Nam Hồng trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Thạch Anh Mặt Trời trong Bộ Sưu Tập Vương Chiêu Bảo
                    	Kích Hoạt năng lượng dụng thần Hành Hoả bằng Ngọc Nam Hồng và Thạch Anh Mặt Trời ( Sunstone ) 
                    -	Ngọc Nam Hồng được mệnh danh là “Ngọc của trái tim”, là viên đá chứa năng lượng Hỏa nhu – ấm áp, từ bi, và tinh tế.
                    Không bùng cháy dữ dội như Hỏa Dương, mà lan tỏa âm ấm, dịu nhẹ như ánh hoàng hôn, giúp con người phục hồi cảm xúc, tăng khả năng yêu thương, và kích hoạt năng lượng sống tích cực.
                    -	Thạch Anh Mặt Trời được mệnh danh là “Viên đá của Thái Dương”, mang năng lượng Hỏa mạnh mẽ nhất trong thế giới khoáng vật.
                    Ánh cam ánh vàng tựa mặt trời rực rỡ không chỉ tượng trưng cho nguồn sáng, mà còn là trường khí của sự sống, tự tin và thịnh vượng.trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng tình duyên, thu hút tình cảm, cải thiện mối quan hệ hoặc mở rộng kết nối nhờ Charm Trứng Bách Duyên
                    Trong phong thuỷ, Nhân Duyên và Mối Quan Hệ là phần năng lượng mềm nhưng có sức ảnh hưởng mạnh nhất đến vận trình của mỗi người.
                    Một ngừoi có tài, có trí mà không có duyên sẽ thường gặp khó khăn mà không ai giúp đỡ, cô đơn hoặc lỡ mất những cơ hội quan trọng.
                    Khi năng lượng tình duyên được kích hoạt:
                    -	Tình yêu viên mãn, mối quan hệ hoà hợp, gia đạo yên vui.
                    -	Công việc thuận lợi hơn nhờ quý nhân giúp đỡ, đối tác tin tưởng
                    -	Tâm hồn nhẹ nhàng, thu hút, rạng rỡ - năng lượng toả ra khiến người khác dễ mến
                '

            ],
            'hop_tuoi_ket_hop_lam' => [
                'Tam hợp – Tam hội: Dần – Ngọ – Tuất / Tỵ – Dậu – Sửu',
                'Lục hợp: Ngọ – Mùi',
                'Thời điểm vượng Thủy:	
                    ●	Trong năm: 5/5 – 6/7 dương lịch

                    ●	Trong ngày: 11h – 15h (ngọ – mùi)

                    ●	Hướng Nam

                    ●	Quốc gia vượng Hỏa: Úc, Tây Ban Nha, Ai Cập, Ấn Độ

                    ●	Thành phố: Bangkok, Đà Nẵng, Sevilla (Tây Ban Nha) 

                '
            ],
            'ban_than_hanh_vi' => [
                'Thường xuyên tắm nắng sáng sớm (7h – 9h)',
                'Luyện tập thể dục, đặc biệt là cardio, HIIT, thể thao vận động mạnh',
                'Ngủ sớm, dậy sớm – tránh thức khuya (thuộc Thủy)',
                'Ăn cay vừa phải để kích hoạt Hỏa khí',
                'Tránh không gian lạnh, ẩm, ánh sáng yếu',

            ],
            'ket_luan' => 'Người có mệnh cần Hỏa cần chủ động đưa nhiều yếu tố ánh sáng, năng lượng, vận động và màu nóng vào đời sống hằng ngày.
 Tăng Hỏa giúp cải thiện khí huyết, tăng quyết đoán, giảm trì trệ, lo âu.
Ngành nghề cần biểu đạt, kỹ năng mềm, lãnh đạo hoặc hoạt động liên quan nhiệt, ánh sáng, công nghệ đều phù hợp.
'
        ],
        'Thổ' => [
            'tinh_chat_ngu_hanh' => 'Thổ chủ về trung hòa, ổn định, tín nghĩa và sự kiên trì. Người có Dụng Thần là Thổ thường cần tăng cường yếu tố giữ vững, bền bỉ, biết lo xa, xử lý vấn đề theo hướng ổn định thay vì thay đổi liên tục. Hành Thổ cũng đại diện cho vùng đất, nền móng, sự an định, tĩnh tại.
Người cần Thổ nên sống cuộc sống quy củ, có kế hoạch rõ ràng. Khi Thổ đủ sẽ giúp thân mệnh ổn định, dễ phát huy thực lực, ít gặp biến động bất ngờ.
',
            'nganh_nghe_phu_hop' => [
                ['key' => 'Bất động sản, xây dựng, kiến trúc, hạ tầng kỹ thuật', 'value' => ''],
                ['key' => 'Địa ốc, quản lý đất đai, vật liệu xây dựng, thiết kế công trình', 'value' => ''],
                ['key' => 'Nông nghiệp, trồng trọt, chăn nuôi (gắn với đất)', 'value' => ''],
                ['key' => 'Gốm sứ, đồ đất nung, các sản phẩm chế tác từ đất, đá', 'value' => ''],
                ['key' => 'Kinh doanh kho vận, logistics, chuỗi cung ứng', 'value' => ''],
                ['key' => 'Các ngành nghề liên quan đến tài chính – kế toán – quản lý – hành chính (vì Thổ chủ trung tâm, quản lý, kiểm soát)', 'value' => ''],
                ['key' => 'Luật, giáo dục, tôn giáo (Thổ đại diện cho sự trật tự, quy tắc)', 'value' => '']
            ],
            'mau_sac_nen_dung' => [
                ['key' => 'Vàng đất, vàng nhạt, nâu đất, nâu cà phê', 'value' => ''],
                ['key' => 'Cam đất, be nhạt, nâu đỏ trầm', 'value' => ''],
                ['key' => 'Chú ý', 'value' => 'Không nên dùng quá nhiều màu trắng sáng (thuộc Kim), hay màu xanh lá (thuộc Mộc – khắc Thổ).'],
            ],
            'trang_suc_vat_pham' => [
                'Sim phong thuỷ',
                'Số tài khoản phong thuỷ',
                '●	Vật phẩm cải vận 1
                    ○	Vòng tay Thạch Anh Tóc Nâu trong Bộ Sưu Tập Vương Chiêu Bảo
                    ○	Vòng chuỗi Thạch Anh Tóc Vàng trong Bộ Sưu Tập Vương Chiêu Bảo
                    	Kích Hoạt năng lượng dụng thần hành Thổ của bạn bằng Thạch Anh Tóc Nâu và Thạch Anh Tóc Vàng
                    -	Thạch Anh Tóc Vàng là loại ngọc quý hiếm chứa các sợi rutile ánh kim vàng nằm bên trong tinh thể trong suốt – tượng trưng cho ánh sáng của Mặt Trời được bao bọc trong lòng đất.
                    Đây là biểu tượng hoàn hảo của năng lượng Thổ – nguồn gốc của tài vận, của sự thịnh vượng và lòng kiên định.
                    -	Thạch Anh Tóc Nâu là viên ngọc quý mang linh khí của Đất Mẹ, tượng trưng cho sự vững vàng, lòng tin và sức mạnh nội tâm.
                    Đây là viên đá đặc biệt dành cho người cần kích hoạt Dụng Thần hành Thổ, giúp củng cố năng lượng gốc, quy tụ tài khí và mở ra vận trình ổn định, thịnh vượng dài lâu.
                    	Kích hoạt năng lượng của năng lượng thịnh vượng và tiền bạc hanh thông nhờ biểu tượng Charm Thiềm Thừ, linh vật chiêu tài hàng đầu trong phong thuỷ Á Đông. Giúp chủ nhân chiêu tài – tụ bảo – tránh hao tài tán lộc.
                    Trong phong thuỷ, năng lượng tài khí không tự nhiên mà có, nó cần được “dẫn đường và kích hoạt”. Nếu không bản thân sẽ dễ gặp thất thoát tài chính, tiền vào ít, ra nhiều. 
                    Khi năng lượng tài lộc được kích hoạt đúng cách, bạn sẽ:
                    -	Thu hút cơ hội kinh doanh, đối tác và khách hàng
                    -	Giữ được tiền của, tránh hao tổn
                    -	Tăng tần số thịnh vượng trong tư duy và quyết định
                ',
                '●	Vật Phẩm cải vận 2:
                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Kim Quy
                    ○	Vòng chuỗi Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Kim Quy 
                    	Kích Hoạt năng lượng dụng thần hành Thuỷ của bạn bằng Lam Ngọc Phỉ Thuý - một trong những viên đá mang trường năng lượng Thuỷ mạnh nhất.
                    Màu lam ngọc dịu sâu, trong trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng Trường Thọ nhờ biểu tượng Charm THẦN KIM QUY.
                    Trong Ngũ Hành, thân thể con người là một tiểu vũ trụ. Khi năng lượng bị mất cân bằng, cơ thể sẽ suy yếu, tinh thần giảm sút, trí tuệ mờ mịt. 
                    Việc kích hoạt năng lượng Trường Thọ giúp:
                    -	Tăng sinh khí, hỗ trợ sức khoẻ toàn thân
                    -	Giữ tâm an định, giảm căng thẳng
                    -	Củng cố năng lượng DƯƠNG, giúp tinh thần tỉnh táo và sáng suốt
                    -	Chảm Kim Quy như 1 vật bảo hộ cho trường khí của chủ nhân, giúp tránh năng lượng xấu xâm nhập.
                ',
                '●	Vật Phẩm cải vận 3:
                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Bách Duyên

                    ○	Vòng tay Lam Ngọc Phỉ Thuý trong Bộ Sưu Tập Vương Bách Duyên

                    	Kích Hoạt năng lượng dụng thần hành Thuỷ của bạn bằng Lam Ngọc Phỉ Thuý - một trong những viên đá mang trường năng lượng Thuỷ mạnh nhất.
                    Màu lam ngọc dịu sâu, trong trẻo như làn nước sớm, tượng trưng cho sự tĩnh tại, thông tuệ và thanh lọc.
                    	Kích Hoạt năng lượng tình duyên, thu hút tình cảm, cải thiện mối quan hệ hoặc mở rộng kết nối nhờ Charm Trứng Bách Duyên
                    Trong phong thuỷ, Nhân Duyên và Mối Quan Hệ là phần năng lượng mềm nhưng có sức ảnh hưởng mạnh nhất đến vận trình của mỗi người.
                    Một ngừoi có tài, có trí mà không có duyên sẽ thường gặp khó khăn mà không ai giúp đỡ, cô đơn hoặc lỡ mất những cơ hội quan trọng.
                    Khi năng lượng tình duyên được kích hoạt:
                    -	Tình yêu viên mãn, mối quan hệ hoà hợp, gia đạo yên vui.
                    -	Công việc thuận lợi hơn nhờ quý nhân giúp đỡ, đối tác tin tưởng
                    -	Tâm hồn nhẹ nhàng, thu hút, rạng rỡ - năng lượng toả ra khiến người khác dễ mến
                '
            ],
            'hop_tuoi_ket_hop_lam' => [
                'Người có mệnh cần Thổ nên giao lưu, hợp tác với người mang hành Hỏa hoặc Thổ (vì Hỏa sinh Thổ, Thổ trợ Thổ). Cụ thể:
                    ●	Người sinh vào các tháng Hỏa – Thổ vượng: tháng 5, 6, 9 âm lịch

                    ●	Người có tuổi Ngọ, Mùi, Thìn, Tuất (tứ hành xung Thổ – Hỏa)

                    ●	Người làm nghề liên quan đến xây dựng, bất động sản, kế toán
                '
            ],
            'ban_than_hanh_vi' => [
                'Giữ lối sống điều độ, không thức khuya, không ăn uống thất thường',
                'Làm việc theo lịch cố định, phân chia thời gian rõ ràng',
                'Nên dùng lịch giấy, bảng kế hoạch cá nhân để ghi chép từng tuần',
                'Chọn đồng hồ mặt vuông, màu nâu hoặc vàng đất',
                'Mua sắm nên chọn đồ có hình khối vuông vức, chất liệu chắc chắn',
                'Ăn uống đúng giờ, dùng đồ ấm nóng, hạn chế đồ nguội'

            ],
            'ket_luan' => 'Người có mệnh cần Thổ cần sống ổn định, duy trì thói quen có quy luật, sử dụng các vật phẩm, thực phẩm và môi trường mang tính "ổn định – nền tảng".
Việc cải vận hiệu quả nhất là thông qua:
●	Dùng màu sắc – vật liệu – chất liệu thuộc hành Thổ trong trang phục và không gian sống

●	Làm việc trong các ngành bền vững, ổn định

●	Kết thân – hợp tác với người mệnh Hỏa hoặc Thổ

●	Ăn uống, sinh hoạt đúng giờ, bổ sung thực phẩm có màu và khí Thổ

Thổ là hành mang lại sự “dưỡng sinh” cho toàn cục, nên cải mệnh bằng hành Thổ có thể duy trì hiệu quả lâu dài, ổn định và an toàn.
'
        ]
    ];

    protected static $chineseStemMap = [
        '甲' => 'Giáp',
        '乙' => 'Ất',
        '丙' => 'Bính',
        '丁' => 'Đinh',
        '戊' => 'Mậu',
        '己' => 'Kỷ',
        '庚' => 'Canh',
        '辛' => 'Tân',
        '壬' => 'Nhâm',
        '癸' => 'Quý'
    ];

    protected static $chineseBranchMap = [
        '子' => 'Tý',
        '丑' => 'Sửu',
        '寅' => 'Dần',
        '卯' => 'Mão',
        '辰' => 'Thìn',
        '巳' => 'Tỵ',
        '午' => 'Ngọ',
        '未' => 'Mùi',
        '申' => 'Thân',
        '酉' => 'Dậu',
        '戌' => 'Tuất',
        '亥' => 'Hợi'
    ];

    // Can tàng (ẩn can)
    protected static $hiddenStems = [
        'Tý'  => ['Quý'],
        'Sửu' => ['Kỷ', 'Quý', 'Tân'],
        'Dần' => ['Giáp', 'Bính', 'Mậu'],
        'Mão' => ['Ất'],
        'Thìn' => ['Mậu', 'Ất', 'Quý'],
        'Tỵ'  => ['Bính', 'Canh', 'Mậu'],
        'Ngọ' => ['Đinh', 'Kỷ'],
        'Mùi' => ['Kỷ', 'Đinh', 'Ất'],
        'Thân' => ['Canh', 'Nhâm', 'Mậu'],
        'Dậu' => ['Tân'],
        'Tuất' => ['Mậu', 'Tân', 'Đinh'],
        'Hợi' => ['Nhâm', 'Giáp'],
    ];

    // Ngũ hành của Thiên Can
    protected static $stemElements = [
        'Giáp' => 'Mộc',
        'Ất' => 'Mộc',
        'Bính' => 'Hỏa',
        'Đinh' => 'Hỏa',
        'Mậu' => 'Thổ',
        'Kỷ' => 'Thổ',
        'Canh' => 'Kim',
        'Tân' => 'Kim',
        'Nhâm' => 'Thủy',
        'Quý' => 'Thủy',
    ];

    // Âm Dương của Thiên Can (0 = Dương, 1 = Âm)
    protected static $stemYinYang = [
        'Giáp' => 0,
        'Bính' => 0,
        'Mậu' => 0,
        'Canh' => 0,
        'Nhâm' => 0,
        'Ất' => 1,
        'Đinh' => 1,
        'Kỷ' => 1,
        'Tân' => 1,
        'Quý' => 1,
    ];

    protected static $tsStart = [
        'Giáp' => 'Hợi',
        'Ất' => 'Hợi',     // Mộc
        'Bính' => 'Dần',
        'Đinh' => 'Dần',   // Hỏa
        'Mậu' => 'Thân',
        'Kỷ' => 'Thân',    // Thổ
        'Canh' => 'Tỵ',
        'Tân' => 'Tỵ',      // Kim
        'Nhâm' => 'Thân',
        'Quý' => 'Thân'   // Thủy
    ];

    protected static $truongSinhCycle = [
        'Giáp' => [
            'Tý' => 'Mộc Dục',
            'Sửu' => 'Quan Đới',
            'Dần' => 'Lâm Quan',
            'Mão' => 'Đế Vượng',
            'Thìn' => 'Suy',
            'Tỵ' => 'Bệnh',
            'Ngọ' => 'Tử',
            'Mùi' => 'Mộ',
            'Thân' => 'Tuyệt',
            'Dậu' => 'Thai',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Trường Sinh'

        ],
        'Ất' => [
            'Tý' => 'Bệnh',
            'Sửu' => 'Suy',
            'Dần' => 'Đế Vượng',
            'Mão' => 'Lâm Quan',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Mộc Dục',
            'Ngọ' => 'Trường Sinh',
            'Mùi' => 'Dưỡng',
            'Thân' => 'Thai',
            'Dậu' => 'Tuyệt',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tử'
        ],     // Mộc
        'Bính' => [
            'Tý' => 'Thai',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Trường Sinh',
            'Mão' => 'Mộc Dục',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Lâm Quan',
            'Ngọ' => 'Đế Vượng',
            'Mùi' => 'Suy',
            'Thân' => 'Bệnh',
            'Dậu' => 'Tử',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tuyệt'
        ],
        'Đinh' => [
            'Tý' => 'Tuyệt',
            'Sửu' => 'Mộ',
            'Dần' => 'Tử',
            'Mão' => 'Bệnh',
            'Thìn' => 'Suy',
            'Tỵ' => 'Đế Vượng',
            'Ngọ' => 'Lâm Quan',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Mộc Dục',
            'Dậu' => 'Trường Sinh',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Thai'
        ],   // Hỏa
        'Mậu' => [
            'Tý' => 'Thai',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Trường Sinh',
            'Mão' => 'Mộc Dục',
            'Thìn' => 'Quan Đới',
            'Tỵ' => 'Lâm Quan',
            'Ngọ' => 'Đế Vượng',
            'Mùi' => 'Suy',
            'Thân' => 'Bệnh',
            'Dậu' => 'Tử',
            'Tuất' => 'Mộ',
            'Hợi' => 'Tuyệt'
        ],
        'Kỷ' => [
            'Tý' => 'Tuyệt',
            'Sửu' => 'Mộ',
            'Dần' => 'Tử',
            'Mão' => 'Bệnh',
            'Thìn' => 'Suy',
            'Tỵ' => 'Đế Vượng',
            'Ngọ' => 'Lâm Quan',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Mộc Dục',
            'Dậu' => 'Trường Sinh',
            'Tuất' => 'Dưỡng',
            'Hợi' => 'Thai'
        ],    // Thổ
        'Canh' => [
            'Tý' => 'Tử',
            'Sửu' => 'Mộ',
            'Dần' => 'Tuyệt',
            'Mão' => 'Thai',
            'Thìn' => 'Dưỡng',
            'Tỵ' => 'Trường Sinh',
            'Ngọ' => 'Mộc Dục',
            'Mùi' => 'Quan Đới',
            'Thân' => 'Lâm Quan',
            'Dậu' => 'Đế Vượng',
            'Tuất' => 'Suy',
            'Hợi' => 'Bệnh'
        ],
        'Tân' => [
            'Tý' => 'Trường Sinh',
            'Sửu' => 'Dưỡng',
            'Dần' => 'Thai',
            'Mão' => 'Tuyệt',
            'Thìn' => 'Mộ',
            'Tỵ' => 'Tử',
            'Ngọ' => 'Bệnh',
            'Mùi' => 'Suy',
            'Thân' => 'Đế Vượng',
            'Dậu' => 'Lâm Quan',
            'Tuất' => 'Quan Đới',
            'Hợi' => 'Mộc Dục'
        ],      // Kim
        'Nhâm' => [
            'Tý' => 'Đế Vượng',
            'Sửu' => 'Suy',
            'Dần' => 'Bệnh',
            'Mão' => 'Tử',
            'Thìn' => 'Mộ',
            'Tỵ' => 'Tuyệt',
            'Ngọ' => 'Thai',
            'Mùi' => 'Dưỡng',
            'Thân' => 'Trường Sinh',
            'Dậu' => 'Mộc Dục',
            'Tuất' => 'Quan Đới',
            'Hợi' => 'Lâm Quan'
        ],
        'Quý' => [
            'Tý' => 'Lâm Quan',
            'Sửu' => 'Quan Đới',
            'Dần' => 'Mộc Dục',
            'Mão' => 'Trường Sinh',
            'Thìn' => 'Dưỡng',
            'Tỵ' => 'Thai',
            'Ngọ' => 'Tuyệt',
            'Mùi' => 'Mộ',
            'Thân' => 'Tử',
            'Dậu' => 'Bệnh',
            'Tuất' => 'Suy',
            'Hợi' => 'Đế Vượng'
        ]   // Thủy
    ];

    // Nạp Âm 60 Giáp Tý
    protected static $napAm = [
        'GiápTý' => 'Hải Trung Kim',
        'ẤtSửu' => 'Hải Trung Kim',
        'BínhDần' => 'Lư Trung Hỏa',
        'ĐinhMão' => 'Lư Trung Hỏa',
        'MậuThìn' => 'Đại Lâm Mộc',
        'KỷTỵ' => 'Đại Lâm Mộc',
        'CanhNgọ' => 'Lộ Bàng Thổ',
        'TânMùi' => 'Lộ Bàng Thổ',
        'NhâmThân' => 'Kiếm Phong Kim',
        'QuýDậu' => 'Kiếm Phong Kim',
        'GiápTuất' => 'Sơn Đầu Hỏa',
        'ẤtHợi' => 'Sơn Đầu Hỏa',
        'BínhTý' => 'Giản Hạ Thủy',
        'ĐinhSửu' => 'Giản Hạ Thủy',
        'MậuDần' => 'Thành Đầu Thổ',
        'KỷMão' => 'Thành Đầu Thổ',
        'CanhThìn' => 'Bạch Lạp Kim',
        'TânTỵ' => 'Bạch Lạp Kim',
        'NhâmNgọ' => 'Dương Liễu Mộc',
        'QuýMùi' => 'Dương Liễu Mộc',
        'GiápThân' => 'Tuyền Trung Thủy',
        'ẤtDậu' => 'Tuyền Trung Thủy',
        'BínhTuất' => 'Ốc Thượng Thổ',
        'ĐinhHợi' => 'Ốc Thượng Thổ',
        'MậuTý' => 'Tích Lịch Hỏa',
        'KỷSửu' => 'Tích Lịch Hỏa',
        'CanhDần' => 'Tùng Bách Mộc',
        'TânMão' => 'Tùng Bách Mộc',
        'NhâmThìn' => 'Trường Lưu Thủy',
        'QuýTỵ' => 'Trường Lưu Thủy',
        'GiápNgọ' => 'Sa Trung Kim',
        'ẤtMùi' => 'Sa Trung Kim',
        'BínhThân' => 'Sơn Hạ Hỏa',
        'ĐinhDậu' => 'Sơn Hạ Hỏa',
        'MậuTuất' => 'Bình Địa Mộc',
        'KỷHợi' => 'Bình Địa Mộc',
        'CanhTý' => 'Bích Thượng Thổ',
        'TânSửu' => 'Bích Thượng Thổ',
        'NhâmDần' => 'Kim Bạch Kim',
        'QuýMão' => 'Kim Bạch Kim',
        'GiápThìn' => 'Phúc Đăng Hỏa',
        'ẤtTỵ' => 'Phúc Đăng Hỏa',
        'BínhNgọ' => 'Thiên Hà Thủy',
        'ĐinhMùi' => 'Thiên Hà Thủy',
        'MậuThân' => 'Đại Dịch Thổ',
        'KỷDậu' => 'Đại Dịch Thổ',
        'CanhTuất' => 'Thoa Xuyến Kim',
        'TânHợi' => 'Thoa Xuyến Kim',
        'NhâmTý' => 'Tang Đố Mộc',
        'QuýSửu' => 'Tang Đố Mộc',
        'GiápDần' => 'Đại Khê Thủy',
        'ẤtMão' => 'Đại Khê Thủy',
        'BínhThìn' => 'Sa Trung Thổ',
        'ĐinhTỵ' => 'Sa Trung Thổ',
        'MậuNgọ' => 'Thiên Thượng Hỏa',
        'KỷMùi' => 'Thiên Thượng Hỏa',
        'CanhThân' => 'Thạch Lựu Mộc',
        'TânDậu' => 'Thạch Lựu Mộc',
        'NhâmTuất' => 'Đại Hải Thủy',
        'QuýHợi' => 'Đại Hải Thủy',
    ];

    protected static $quansinhan = [
        ['', 'Quan Sát', 'Ấn Tinh', '', '', '', '', '', '', ''],
        ['', '', 'Ấn Tinh', '', 'Quan Sát', '', '', '', '', ''],
        ['', '', 'Ấn Tinh', '', '', '', '', 'Quan Sát', '', ''],
        ['', '', '', '', '', '', '', 'Quan Sát', 'Ấn Tinh', ''],
        ['', '', '', '', '', '', '', '', 'Ấn Tinh', 'Quan Sát'],
        ['', '', 'Quan Sát', '', '', '', '', 'Ấn Tinh', '', ''],
        ['', '', '', '', '', '', '', 'Ấn Tinh', 'Quan Sát', ''],
        ['', '', '', '', '', '', 'Quan Sát', 'Ấn Tinh', '', ''],
        ['', '', 'Quan Sát', '', 'Ấn Tinh', '', '', '', '', ''],
        ['', '', '', '', 'Ấn Tinh', '', '', '', '', 'Quan Sát'],
        ['Ấn Tinh', '', '', '', '', 'Quan Sát', '', '', '', ''],
        ['', '', '', '', '', 'Quan Sát', '', '', 'Ấn Tinh', ''],
        ['', '', '', '', 'Quan Sát', '', '', 'Ấn Tinh', '', ''],
        ['Quan Sát', '', 'Ấn Tinh', '', '', '', '', '', '', ''],
        ['Quan Sát', '', '', '', 'Ấn Tinh', '', '', '', '', ''],
        ['Ấn Tinh', '', 'Quan Sát', '', '', '', '', '', '', ''],
        ['Ấn Tinh', '', '', '', 'Quan Sát', '', '', '', '', ''],
        ['Ấn Tinh', 'Quan Sát', '', '', '', '', '', '', '', ''],
    ];

    protected static $thansinhthuong  = [
        [
            '',
            '',
            'Tài Tinh',
            '',
            'Thực Thương',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            'Thực Thương',
            '',
            '',
            '',
            '',
            'Tài Tinh'
        ],
        [
            '',
            '',
            'Thực Thương',
            '',
            'Tài Tinh',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            'Tài Tinh',
            'Thực Thương',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            'Thực Thương',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            'Thực Thương',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Thực Thương',
            'Tài Tinh'
        ],
        [
            '',
            '',
            'Tài Tinh',
            '',
            '',
            '',
            '',
            'Thực Thương',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            'Thực Thương',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Thực Thương',
            'Tài Tinh',
            ''
        ],
        [
            'Tài Tinh',
            '',
            'Thực Thương',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            'Tài Tinh',
            '',
            '',
            '',
            'Thực Thương',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            '',
            '',
            'Thực Thương',
            ''
        ],
        [
            'Thực Thương',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Tài Tinh',
            '',
            'Thực Thương',
            '',
            ''
        ],
        [
            'Thực Thương',
            'Tài Tinh',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            'Thực Thương',
            '',
            'Tài Tinh',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            'Thực Thương',
            '',
            '',
            '',
            'Tài Tinh',
            '',
            '',
            '',
            '',
            ''
        ],
    ];
    protected static $thuongkhacquan = [
        [
            '',
            'Thương Quan',
            '',
            '',
            '',
            '',
            'Chính Quan',
            '',
            '',
            ''
        ],
        [
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            'Thương Quan',
            '',
            '',
            '',
            '',
            'Chính Quan',
            '',
            ''
        ],
        [
            '',
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            '',
            '',
            '',
            'Chính Quan'
        ],
        [
            '',
            '',
            '',
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            'Thương Quan'
        ],
        [
            '',
            'Chính Quan',
            'Thương Quan',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            'Thương Quan',
            'Chính Quan',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            'Chính Quan',
            '',
            'Thương Quan',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            'Thương Quan',
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            'Chính Quan',
            'Thương Quan',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            'Chính Quan',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Chính Quan',
            'Thương Quan',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            'Chính Quan'
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            'Chính Quan',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Chính Quan',
            'Thương Quan'
        ],
        [
            'Thương Quan',
            'Chính Quan',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            'Thương Quan',
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            'Thương Quan',
            '',
            '',
            '',
            'Chính Quan',
            '',
            '',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            'Chính Quan',
            '',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            'Chính Quan',
            '',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            '',
            'Chính Quan',
            ''
        ],
        [
            '',
            '',
            '',
            '',
            '',
            'Thương Quan',
            '',
            '',
            '',
            'Chính Quan'
        ],
    ];


    // Bảng Thập Thần (so sánh Nhật Chủ với Can khác)
    protected static function relation(string $dayStem, string $otherStem): string
    {
        $dayElement  = self::$stemElements[$dayStem]  ?? null;
        $otherElement = self::$stemElements[$otherStem] ?? null;
        if (!$dayElement || !$otherElement) return 'Unknown';

        $dayYin  = self::$stemYinYang[$dayStem];
        $otherYin = self::$stemYinYang[$otherStem];

        // mapping ngũ hành
        $genCycle = [ // sinh
            'Mộc' => 'Hỏa',
            'Hỏa' => 'Thổ',
            'Thổ' => 'Kim',
            'Kim' => 'Thủy',
            'Thủy' => 'Mộc'
        ];
        $ctrlCycle = [ // khắc
            'Mộc' => 'Thổ',
            'Thổ' => 'Thủy',
            'Thủy' => 'Hỏa',
            'Hỏa' => 'Kim',
            'Kim' => 'Mộc'
        ];

        if ($dayElement === $otherElement) {
            return $dayYin === $otherYin ? 'Tỷ' : 'Kiếp';
        }

        // Nhật Chủ sinh ra đối phương
        if ($genCycle[$dayElement] === $otherElement) {
            return $dayYin === $otherYin ? 'Thực' : 'Thương';
        }

        // Đối phương sinh Nhật Chủ
        if ($genCycle[$otherElement] === $dayElement) {
            return $dayYin === $otherYin ? 'Thiên Ấn' : 'Chính Ấn';
        }

        // Nhật Chủ khắc đối phương
        if ($ctrlCycle[$dayElement] === $otherElement) {
            return $dayYin === $otherYin ? 'Thiên Tài' : 'Chính Tài';
        }

        // Đối phương khắc Nhật Chủ
        if ($ctrlCycle[$otherElement] === $dayElement) {
            return $dayYin === $otherYin ? 'Sát' : 'Quan';
        }

        return 'Unknown';
    }

    protected static $DaiVan = null;

    protected static array $yangStems = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];


    protected static function truongSinh(string $dayStem, ?string $branch): ?string
    {
        if (!$branch || !isset(self::$truongSinhCycle[$dayStem])) {
            return 'Không xác định';
        }

        return self::$truongSinhCycle[$dayStem][$branch] ?? 'Không xác định';
    }

    protected static function napAm(string $stem, string $branch): ?string
    {
        $key = $stem . $branch;
        return self::$napAm[$key] ?? null;
    }

    protected static function getHoursToJieqi($birthDateTime, $forward = true)
    {
        $searchDate = $birthDateTime->format('Y-m-d');
        $searchTime = $birthDateTime->format('H:i:s');

        // Tìm bản ghi nạp giáp gần nhất
        $napGiap = \App\Models\NapGiap::where('thoi_diem_bat_dau_ngay', '<=', $searchDate)
            ->where(function ($query) use ($searchDate, $searchTime) {
                $query->where('thoi_diem_bat_dau_ngay', '<', $searchDate)
                    ->orWhere(function ($q) use ($searchDate, $searchTime) {
                        $q->where('thoi_diem_bat_dau_ngay', '=', $searchDate)
                            ->where('thoi_diem_bat_dau_gio', '<=', $searchTime);
                    });
            })
            ->orderBy('thoi_diem_bat_dau_ngay', 'desc')
            ->orderBy('thoi_diem_bat_dau_gio', 'desc')
            ->first();

        if (!$napGiap) {
            return 0;
        }

        // Tính thời gian chênh lệch
        $currentTime = new \DateTime($searchDate . ' ' . $searchTime);
        $jieqiTime = new \DateTime($napGiap->thoi_diem_bat_dau_ngay . ' ' . $napGiap->thoi_diem_bat_dau_gio);
        if ($forward) {
            // Dương nam âm nữ: tìm tiết khí tiếp theo
            $nextJieqi = \App\Models\NapGiap::where('thoi_diem_bat_dau_ngay', '>', $searchDate)
                // ->where('thoi_diem_bat_dau_gio', '>', $searchTime)
                ->orderBy('thoi_diem_bat_dau_ngay', 'asc')
                ->orderBy('thoi_diem_bat_dau_gio', 'asc')
                ->first();
            if ($nextJieqi) {
                $endTime = new \DateTime($nextJieqi->thoi_diem_bat_dau_ngay . ' ' . $nextJieqi->thoi_diem_bat_dau_gio);
            } else {
                // Nếu không có tiết khí tiếp theo, lấy thời gian sau 1 tháng
                $endTime = (clone $jieqiTime)->modify('+1 month');
            }
        } else {
            // Âm nam dương nữ: tìm tiết khí trước đó
            $prevJieqi = \App\Models\NapGiap::where('thoi_diem_bat_dau_ngay', '<', $searchDate)
                // ->where('thoi_diem_bat_dau_gio', '<', $searchTime)
                ->orderBy('thoi_diem_bat_dau_ngay', 'desc')
                ->orderBy('thoi_diem_bat_dau_gio', 'desc')
                ->first();

            if ($prevJieqi) {
                $endTime = new \DateTime($prevJieqi->thoi_diem_bat_dau_ngay . ' ' . $prevJieqi->thoi_diem_bat_dau_gio);
            } else {
                // Nếu không có tiết khí trước đó, lấy thời gian trước 1 tháng
                $endTime = (clone $jieqiTime)->modify('-1 month');
            }
        }
        // Tính số giờ chênh lệch
        $diff = $currentTime->diff($endTime);
        $hours = abs($diff->days * 24 + $diff->h + $diff->i / 60);
        return $hours;
    }

    protected static $chuTinh = [];
    protected static $photinh = [];
    protected static $battu = [];
    protected static $cantang = [];
    protected static $cantangdaivan = [];
    protected static $tinhcachnoitam_bang1 = [
        'Giáp' => 'Bản thân có ý chí tiến bộ mạnh mẽ, bề ngoài bình tĩnh nhưng nội tâm luôn suy nghĩ. Là người kiên cường, có cốt khí, cá tính, có ý thức về kinh tế, lòng nhân từ. Thường có cơ hội để làm quản lý, trưởng nhóm hoặc trưởng đơn vị.',
        'Ất' => 'Bản thân có tính cách mềm mại, giỏi phát triển ra xung quanh (phát triển ra nhiều vấn đề, độ chi tiết mỗi vấn đề không cao). Nội tâm tinh tế, đa nghi, ghen tỵ mạnh mẽ, phản ứng nhanh nhậy, ngẫu nhiên, có tính khoan dung nhưng ham muốn chiếm hữu nội tâm cao, có dã tâm nhưng không phô trương.',
        'Bính' => 'Bản thân có tình cảm phong phú, tư tưởng lộ ra, tích cực, nóng nảy, đôi khi hơi lỗ mãng nhưng không hề có tâm kế ẩn dấu. ',
        'Đinh' => 'Bản thân tinh tế và gắn bó, mặt ngoài đoan chính ôn hòa, nội tâm nóng nảy xúc động. Tư duy nhanh nhẹn, tình cảm tinh tế, có lòng trắn ẩn, trách nhiệm, lịch sự nhưng hơi đa nghi.',
        'Mậu' => 'Bản thân có sức chịu đựng, không ngại vất vả, bề ngoài mềm mại, bên trong cứng rắn.',
        'Kỷ' => 'Bản thân bình tĩnh, sẵn sàng cống hiến, đa tài, chân trọng danh dự, giàu cảm xúc, chu đáo nhưng hay nghi ngờ.',
        'Canh' => 'Bản thân cương nhuệ quả cảm, làm người hào sảng có khí phách, có tâm hiệp nghĩa, cá tính hiếu thắng không dễ dàng khuất phục người khác. ',
        'Tân' => 'Bản thân nặng tình cảm mà tính tình không kiên định, lòng thích hư vinh, yêu quyền thế, khí chất tốt, ý chí phấn đấu tiến bộ, không ngừng nâng cao chất lượng cuộc sống.',
        'Nhâm' => 'Bản thân lạc quan, hướng ngoại, không che dấu. Giỏi nắm bắt cơ hội để đưa ra mưu lược, nhưng dễ dàng kích động gây rắc rối. Thông minh nhưng nhiều khi tùy hứng.',
        'Quý' => 'Bản thân mềm mại, nhu thuận, trầm tĩnh, hướng nội, dịu dàng, giàu trí tưởng tượng. Có sức chịu đựng manh mẽ, thân thể tâm lý mẫn cảm, có linh tính.',

    ];

    protected static $tinhcachnoitam_bang2 = [
        //moc
        [
            'Là người nhân nghĩa nhưng khá cố chấp, dễ bị lừa gạt, dễ buồn thương xúc động.',
            'Thích chiếm tiện nghi nhỏ, thiếu sự nhẫn lại và nghị lực, ít vui vẻ.',
            'Tinh thần không ngừng phát triển, đi lên. Đơn giản, liêm chính, tích cực, nhân nghĩa.'
        ],
        //hoa
        [
            'Tính tình nóng nảy, quá ham cầu tiến, trong lòng không giấu được lời nói, thích mạo hiểm và khuếch chương tài phú của mình.',
            'Thiếu nhiệt huyết thậm chí suy nghĩ nhiều bi quan và tiêu cực.',
            'Giàu cảm xúc, tư duy nhanh nhẹn, có lòng hiếu lễ, lòng tự trọng cao, thẳng thắn, quang minh lỗi lạc.',
        ],
        //tho
        [
            'Bướng bỉnh, chậm chạp, không dễ tin người, không hiểu được nhân tình, tính chiếm hữu, tư tâm cao.',
            'Thiếu hưởng thụ cuộc sống hoặc ngay cả khi có tiền cũng không biết cách hưởng thụ cuộc sống.',
            'Có tinh thần nuôi dưỡng, chăm sóc, trung thành, khoan dung, trầm ổn.',
        ],
        //kim
        [
            'Quá cứng rắn, ngạo khí, thiếu cảm xúc, dễ làm tổn thương người khác, xử sự thiếu bình tĩnh.',
            'Hay ưu phiền lo lắng, suy nghĩ thiếu bình tĩnh. ',
            'Tinh thần sâu sắc, tiến bộ nhanh chóng, điềm tĩnh, công bằng, trượng nghĩa.',
        ],
        //thuy
        [
            'Tuỳ hứng, hay thay đổi, dục vọng cao, nhát gan hoặc hay tiêu cực bi quan. ',
            'Tư duy thiếu linh hoạt.',
            'Tâm tính hiếu động, tinh thần tự do, dí dỏm.',
        ],

    ];

    protected static $tinhcachthehientrongcacmoiquanhe = [
        'Tỷ' => 'Có lòng tự trọng mạnh mẽ, làm việc chủ động và tự tin, có trật tự, làm việc từng bước.',
        'Kiếp' => 'Dám nói dám làm, thường sử dụng hành động thực tế để giải quyết vấn đề, hào phóng, khát vong chiếm hữu lớn.',
        'Thực' => 'Thông minh, nhạy bén, bình thản, tập trung chủ yếu vào quá trình mà không để tâm nhiều đến kết quả.',
        'Thương' => 'Có ngạo khí, thích đầu cơ, tự do lãng mạn, đa tài.',
        'Thiên Tài' => 'Là người theo đuổi vật chất, có tầm nhìn sắc xảo, yêu cái đẹp, có đầu óc kinh doanh.',
        'Chính Tài' => 'Bản thân có mục tiêu lâu dài, cạnh tranh công bằng, kiên trì lỗ lực, thu hoạch kết quả bằng chính công sức của mình. Trân trọng thành quả lao động và tiết kiệm.',
        'Chính Quan' => 'Có tính kỷ luật, tự giác, làm việc chăm chỉ, dũng cảm, tinh thần trách nhiệm cao, biết chừng mực không kiêu ngạo.',
        'Quan' => 'Có suy nghĩ nổi loạn, sát phạt quyết đoán, nhiều toan tính.',
        'Thiên Ấn' => 'Giàu cảm xúc nhưng cũng quan tâm đến vật chất, ngoài tĩnh nhưng bên trong động, khôn ngoan nhiều tính toán nên các mối quan hệ giữa các cá nhân trong xã hội không được tốt lắm.',
        'Chính Ấn' => 'Nhân từ, thiện lương, trung hậu, làm người hiền hiếu, yêu thích văn hóa truyền thống. Hay nhận được sự quan tâm từ người khác cũng là người sẵn sàng quan tâm giúp đỡ người khác.',
    ];

    protected static $sochi = [
        'Dần' => 1,
        'Mão' => 2,
        'Thìn' => 3,
        'Tỵ' => 4,
        'Ngọ' => 5,
        'Mùi' => 6,
        'Thân' => 7,
        'Dậu' => 8,
        'Tuất' => 9,
        'Hợi' => 10,
        'Tý' => 11,
        'Sửu' => 12,
    ];

    protected static $luandoanmenhcung = [
        'Tý' => 'Tý cung là Thiên Quý Tinh, có tham vọng phi thường, giàu có và may mắn. Người mang Tý cung có thái độ dễ chịu, tao nhã và duyên dáng, trong cuộc sống hiếm khi thể hiện những biểu hiện tiêu cực hoặc mệt mỏi, họ hoài niệm và giàu tình cảm. Trong chuyện hôn nhân họ là những người vợ, người chồng, yêu thương, thông minh, chân thành, đôi khi họ có tính cách tân. Trong phán đoán sự việc, đôi khi khá chủ quan, đưa ra những quan điểm mới lạ. Ý chí kiên cường chính là ưu điểm của những người này, thường hay nói về sự vật, ít khi nói về người khác và có thái độ rộng lượng.Nếu vào vận không tốt về mặt sức khỏe chú ý đến các bệnh về tim mạch, tuần hoàn máu, suy nhược thần kinh hoặc mệt mỏi bồn chồn.',
        'Sửu' => 'Sửu cung là Thiên Ách Tinh, xa quê cha đất tổ, lúc đầu vất vả khó gặp được may mắn về già sẽ cát tường. Người mang Sửu cung có thái độ vui vẻ, chí hướng mạnh mẽ và rất hoan hỷ khi làm việc công đức. Cuộc sống hay gặp những khó khăn bất kể bản thân có đạt được lợi ích hay không. Nhưng có được điểm tốt là dễ được người khác tín nhiệm, tôn trọng, ngợi ca, bản thân dễ thuyết phục, chỉ dạy được người khác. Tuy nhiên chiếm được xã hội kính ngưỡng chỉ là nhất thời, nếu quá lộ liễu phô trương cuối cùng dễ gặp sự oán giận, mất lòng từ mọi người mà cản trở tới sự nghiệp thăng tiến. Nếu tới vận không tốt, dễ bị các bệnh về thấp khớp, bàn chân, đầu gối yếu, chàm da hoặc đột quỵ.',
        'Dần' => 'Dần cung là Thiên Quyền Tinh, thông minh, có triển vọng, tuổi trung niên có uy quyền. Người mang Dần cung có bản tính kiên quyết, tầm nhìn sắc bén, tràn đầy năng lượng bức phá, thích tìm đường tắt để đạt được mục tiêu của mình. Có khí chất sôi nổi, hoạt bát nên hấp dẫn người khác. Ngay từ nhỏ đã là người cởi mở, nhân từ, sẵn sàng chia sẻ thành quả cùng người khác vì vậy có nhiều bạn bè và nhiều người biết tới. Nếu tới vận không tốt, sức khỏe cần chú ý tới chân và mông, u nhọt, viêm.',
        'Mão' => 'Mão cung là Thiên Xá Tinh, hào phóng trong việc phân phát của cải, khi có được quyền lực, khiêm tốn là ưu tiên hàng đầu. Người mang cung Mão tuy thân thể yếu đuối khi còn trẻ nhưng khi trưởng thành cơ thể có thể trở nên mạnh mẽ. Tuổi trẻ thần kinh quá nhạy bén nên dễ nổi giận, dễ tranh chấp và trải qua nhiều thất bại. khi trưởng thành bản thân nghiêm khắc hơn, tự kiềm chế và tự giác và tự lực. Có sự quan sát mạnh mẽ, có thể đưa ra góc nhìn sâu sắc, hiểu biết về cuộc sống và có điểm khác biệt về sự khám phá thiên nhiên. Nếu tới vận không tốt sức khỏe dễ liên quan tới bộ phận sinh dục, sỏi thận, trĩ, trực tràng, máu.',
        'Thìn' => 'Thìn cung là Thiên Như Tinh, nhiều sự thay đổi, khéo léo. Người mang Thìn cung có tính cách hiền lành, vẻ ngoài lịch lãm, lịch sự trong cư xử, sẵn sàng giải quyết tranh chấp, chu đáo và công bằng trong các biện pháp để cả hai bên cảm thấy hài lòng. Bản thân thường đứng ra giúp đỡ, quá nhiệt tình nên không có thời gian chăm sóc bản thân, thay vào đó dễ gây ra những vướng mắc đúng sai, đặc biệt khi liên quan tới phụ nữ. Ngược lại nếu như bị coi thường họ sẽ không bao giờ cảm thông. Với tính cách này nhiều người sẽ cho rằng bạn là người dễ gần, thiếu quyết đoán, nhưng thực chất là do bản chất muốn giúp đỡ người khác. Nếu tới vận không tốt, sức khỏe dễ ảnh hưởng tới Thận, Thủy Hỏa không hòa hợp, dễ bị đau lưng, Tỳ Thận hư.',
        'Tỵ' => 'Tỵ cung là Thiên Văn Tinh, sự nghiệp văn chương sáng láng, nữ nhân có số mệnh lấy được chồng tốt. Người mang Tỵ cung có tính cách trầm lặng, suy nghĩ thực tế, thích để ý tiểu tiết, thường sợ sự tiếp cận nên cuộc sống cô đơn, đây là một thiệt thòi. Nhiều người tập trung quá nhiều vào các chi tiết nhỏ mà bỏ qua bức tranh lớn, may mắn  là người cung này rất tỉ mỉ, không ngoan và có trật tự và nếu như làm kinh doanh thì tích lũy việc nhỏ để làm việc lớn thì sẽ có tương lai tươi sáng. Thông thường bạn hay phải suy nghĩ khá nhiều nên cần lưu tâm tới sức khỏe tinh thần và giấc ngủ. Nếu tới vận không tốt sức khỏe dễ liên quan đến đường tiêu hóa, táo bón, tiêu chảy.',
        'Ngọ' => 'Ngọ cung là Thiên Phúc Tinh, vinh hoa phú quý và vận may cát tường. Những người mang Ngọ cung sinh ra đã mang đức tính cao thượng, đầy tham vọng và háo hức, có ý chí kiên cường và thường có đủ dũng khí để vượt qua mọi trở ngại bất chấp nguy hiểm, nhưng đôi khi cũng khá kiêu ngạo. Để bứt phá và vượt lên bản thân sẽ không nhượng bộ, khi bình thường thì rất hòa nhã và dễ gần, nhưng đây cũng là phương tiện để bạn có thể thu phục được người khác và tạo điều kiện thuận lợi cho chính mình. Nếu bạn từ bỏ được tính kiêu ngạo thì sẽ dễ dàng thành công hơn. Nếu tới vận không tốt chú ý tới vòng eo, đao cột sống, thấp khớp, vàng da.',
        'Mùi' => 'Mùi cung là Thiên Dịch Tinh, bôn ba vất vả, xa quê cha đất tổ. Những người mang Mùi cung tỏ ra ngại ngùng trong mọi hành động, khiêm nhường, hành động có vẻ rụt rè, nhưng tính tình lại nhạy cảm và dễ xúc động. Bản thân khá nóng nảy, bề ngoài hiền lành, trái tim rất mạnh mẽ, và không dễ dàng chấp nhận ý kiến của người khác, nghiêm khắc và thận trọng trong công việc và là những người thực hiện tốt nhất. Bạn cũng có kỹ năng học tập và quan sát tốt, thích thiền, trí tưởng tượng rất linh hoạt nhưng dễ bị phân tâm nên cần chú ý hơn. Nếu tới vận không tốt, sức khỏe dễ mắc các bệnh về dạ dày, trào ngược, khó tiêu.',
        'Thân' => 'Thân cung là Thiên Cô Tinh, không thích hợp kết hôn sớm, cô đơn, cuộc sống nữ nhân dễ cản trở chồng. Người mang Thân cùng, có hai tính cách song hành, tâm lý khó cân bằng, nên có lúc tự tin  lạc quan, có lúc lại hay nghi ngờ và thất vọng. Tuy nhiên bản thân có lợi thế là luôn có thể đưa ra ý kiến mới lạ, lời nói và cử chỉ giàu cảm xúc nên thu hút được người khác. Khi nói đến sự nghiệp những người mang cung mệnh này cần có sự chuyên tâm, phải bắt đầu từ đầu tới cuối không được bỏ cuộc giữa chừng. Nếu tới vận không tốt sức khỏe cần chú ý các bệnh về Phổi, ngực, ho, hen suyễn, các bệnh về hô hấp, viêm đường tiết liệu.',
        'Dậu' => 'Dậu cung là Thiên Bí Tinh, tính tình chính trực, dễ gặp thị phi. Người mang Dậu cung trầm lặng, tốt bụng, trung thành và đáng tin cậy, nhưng đôi khi cũng dễ nóng giận và cố chấp. Khi còn trẻ ít bộc lộ sự cố chấp, nhưung theo năm tháng tính chủ quan càng trở nên mạnh mẽ và những hành động khó tránh khỏi sự tùy ý. Bạn cần có một người thuyết phục được chính mình và quân sư cho mình thì sẽ đạt được thành tựu trong cuộc sống. Bản thân cũng là người yêu thiên nhiên sông núi, khi khó khăn thì đây cũng là nơi giúp gột rửa, giải tỏa phiền muộn. Nếu tới vận không tốt sức khỏe dễ bị bệnh liên quan tới cổ họng, viêm phế quản, tim mạch, cơ thể dễ bị bồn chồn mất ngủ.',
        'Tuất' => 'Tuất cung là Thiên Nghệ Tinh, có tâm hồn ôn hòa, nổi tiếng về nghệ thuật. Người mang Tuất cung hành động nhanh nhẹn, nhiệt tình trong công việc, có tinh thần dũng cảm, một khi kế hoạch đã sẵn sàng, bạn sẽ thực hiện nó bằng tất cả sức lực của mình. Nhưng bản thân thiếu tính kiên nhẫn, nên cần có tính kiểm soát để đưa sự nhiệt huyết vào đúng hướng thì cuối cùng mới thành công. Thỉnh thoảng bạn dễ bị kích thíc, tuy nhiên hãy giữ cho mình một cái đầu lạnh và suy nghĩ thật kỹ thì chỉ cần không nản lòng bạn sẽ thu được kết quả. Nếu tới vận không tốt sức khỏe cần chú ý tới đầu, chẳng hạn như chóng mặt, đột quỵ, suy giảm trí nhớ, đau răng.',
        'Hợi' => 'Hợi cung là Thiên Thọ Tinh, nhân hậu, khai sáng, hết lòng giúp đỡ người khác. Người mang Hợi cung có tình cảm vô cùng mạnh mẽ và thần kinh nhạy bén nên đối xử với mọi người, mọi việc bằng sự nhiệt tình. Nếu mọi việc vượt quá giới hạn dễ gặp sự hiểu lầm và buộc tội từ người khác. Bản thân khiêm tốn và bao dung, trong cuộc sống tiền bạc không phải vấn đề bản thân đặt nặng, sẵn sàng bỏ qua để có cuộc sống tốt nhất với người thân và bạn bè. Nếu tới vận không tốt cần chú ý tới bệnh gút do cảm lạnh, sưng tấy, viêm da, viêm khớp, tê buốt tứ chi.',
    ];

    public static function calc(int $y, int $m, int $d, ?int $hour = null, ?int $minute = null, $g = 'male', $y_detail): array
    {
        // date_default_timezone_set('PRC');
        $calendar = new Calendar();
        $result = $calendar->solar($y, $m, $d, $hour);
        $conv = function (?string $ganzhi) {
            if (!$ganzhi) return [null, null];
            $stemCh = mb_substr($ganzhi, 0, 1, 'UTF-8');
            $branchCh = mb_substr($ganzhi, 1, 1, 'UTF-8');
            return [
                self::$chineseStemMap[$stemCh] ?? null,
                self::$chineseBranchMap[$branchCh] ?? null
            ];
        };

        $searchDate = sprintf("%04d-%02d-%02d", $y, $m, $d);
        $searchTime = $hour !== null ? sprintf("%02d:%02d:00", $hour, $minute ?? 0) : '00:00:00';

        // Tìm bản ghi gần nhất và nhỏ hơn hoặc bằng thời điểm cần tra
        $napGiap = \App\Models\NapGiap::where('thoi_diem_bat_dau_ngay', '<=', $searchDate)
            ->where(function ($query) use ($searchDate, $searchTime) {
                $query->where('thoi_diem_bat_dau_ngay', '<', $searchDate)
                    ->orWhere(function ($q) use ($searchDate, $searchTime) {
                        $q->where('thoi_diem_bat_dau_ngay', '=', $searchDate)
                            ->where('thoi_diem_bat_dau_gio', '<=', $searchTime);
                    });
            })
            ->orderBy('thoi_diem_bat_dau_ngay', 'desc')
            ->orderBy('thoi_diem_bat_dau_gio', 'desc')
            ->first();
        if (!$napGiap) {
            [$yearStem, $yearBranch] = $conv($result['ganzhi_year']  ?? $result['ganzhiYear']  ?? null);
            [$monthStem, $monthBranch] = $conv($result['ganzhi_month'] ?? $result['ganzhiMonth'] ?? null);
            $songay = 1;
        } else {
            $yearStem = explode(' ', $napGiap->nap_giap_nam)[0];
            $yearBranch = explode(' ', $napGiap->nap_giap_nam)[1];
            $monthStem = explode(' ', $napGiap->nap_giap_thang)[0];
            $monthBranch = explode(' ', $napGiap->nap_giap_thang)[1];
            $ngaybatdau = new DateTime($napGiap->thoi_diem_bat_dau_ngay);
            $ngaysinh = new DateTime($searchDate);
            $songay = $ngaybatdau->diff($ngaysinh)->days;
        }
        [$dayStem, $dayBranch] = $conv($result['ganzhi_day']   ?? $result['ganzhiDay']   ?? null);
        [$hourStem, $hourBranch] = $conv($result['ganzhi_hour']  ?? $result['ganzhiHour']  ?? null);

        // nếu giờ null thì tự tính
        if (!$hourStem && $hour !== null) {
            $dayCanIndex = array_search($dayStem, self::$stems, true) ?: 0;
            $chiIndex = intdiv($hour + 1, 2) % 12;
            $hourBranch = self::$branches[$chiIndex];
            $hourCanIndex = ($dayCanIndex * 2 + $chiIndex) % 10;
            $hourStem = self::$stems[$hourCanIndex];
        }

        $pillars = [
            'year'  => ['can' => $yearStem,  'chi' => $yearBranch],
            'month' => ['can' => $monthStem, 'chi' => $monthBranch],
            'day'   => ['can' => $dayStem,   'chi' => $dayBranch],
            'hour'  => ['can' => $hourStem,  'chi' => $hourBranch],
        ];
        self::$battu = $pillars;

        // Tính Can Tàng & Phó Tinh
        $cantang = [];
        $photin = [];
        foreach ($pillars as $k => $p) {
            $chi = $p['chi'];
            $cantang[$k] = $chi ? (self::$hiddenStems[$chi] ?? []) : [];
            foreach ($cantang[$k] as $stem) {
                $photin[$k][$stem] = self::relation($dayStem, $stem);
            }
        }
        self::$cantang = $cantang;

        self::$photinh = array_values(array_map('array_values', $photin));

        $truongSinh = [];
        foreach ($pillars as $k => $p) {
            $truongSinh[$k] = self::truongSinh($dayStem, $p['chi']);
        }

        $napAm = [];
        foreach ($pillars as $k => $p) {
            if ($p['can'] && $p['chi']) {
                $napAm[$k] = self::napAm($p['can'], $p['chi']);
            }
        }
        $chuTinh = [
            'year'  => self::getChuTinhWebVN($dayStem, $yearStem),
            'month' => self::getChuTinhWebVN($dayStem, $monthStem),
            'day'   => 'Nhật Can',
            'hour'  => self::getChuTinhWebVN($dayStem, $hourStem),
        ];

        self::$chuTinh = $chuTinh;


        $diachi_cantang = [
            [
                'diachi' => $pillars['year']['chi'],
                'cantang' => $cantang['year'],
            ],
            [
                'diachi' => $pillars['month']['chi'],
                'cantang' => $cantang['month'],
            ],
            [
                'diachi' => $pillars['day']['chi'],
                'cantang' => $cantang['day'],
            ],
            [
                'diachi' => $pillars['hour']['chi'],
                'cantang' => $cantang['hour'],
            ],
        ];

        $_inputthiencan = [
            $pillars['year']['can'],
            $pillars['month']['can'],
            $pillars['day']['can'],
            $pillars['hour']['can']
        ];
        $_inputdiachi = [
            $pillars['year']['chi'],
            $pillars['month']['chi'],
            $pillars['day']['chi'],
            $pillars['hour']['chi']
        ];
        // Tính số giờ đến tiết khí
        $birthDateTime = new \DateTime(sprintf("%04d-%02d-%02d %02d:%02d:00", $y, $m, $d, $hour ?? 0, $minute ?? 0));

        // Xác định âm dương của can tháng để biết tính thuận hay nghịch
        $yangStems = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];
        $isYang = in_array($yearStem, $yangStems, true);
        $forward = (($isYang && $g == 'male') || (!$isYang && $g == 'female'));
        // Tính số giờ đến tiết khí
        $hoursToJieqi = self::getHoursToJieqi($birthDateTime, $forward);

        // Tính đại vận
        $daivanmuoinam = self::getDaiVan($birthDateTime->format('Y-m-d'), $monthStem, $monthBranch, $y_detail, $forward, $hoursToJieqi);
        // dd($daivanmuoinam);
        self::$cantangdaivan = $daivanmuoinam['can_tang_dai_van'];
        $dungthan = self::dungthan($daivanmuoinam['can'] . " " . $daivanmuoinam['chi'], $_inputthiencan, $diachi_cantang, $d, $songay);
        $getMenh = self::getMenhTinh($dayStem);

        $tinhan = [
            [
                'sao' => $getMenh[0],
                'diem' => $dungthan[0],
                'menh' => 'Mộc'
            ],
            [
                'sao' => $getMenh[1],
                'diem' => $dungthan[1],
                'menh' => 'Hỏa'
            ],
            [
                'sao' => $getMenh[2],
                'diem' => $dungthan[2],
                'menh' => 'Thổ'
            ],
            [
                'sao' => $getMenh[3],
                'diem' => $dungthan[3],
                'menh' => 'Kim'
            ],
            [
                'sao' => $getMenh[4],
                'diem' => $dungthan[4],
                'menh' => 'Thủy'
            ],
        ];
        $thanSatService = new ThanSatService();
        $thanSat = $thanSatService->calcThanSat($pillars, $g);
        $getDungThanKyThan = self::getDungkyThan();
        $tutruthapthan = [$chuTinh['year'], $chuTinh['month'], 'Tỷ', $chuTinh['hour']];
        $photinh_thapthan = [self::$photinh[0][0], self::$photinh[1][0], self::$photinh[2][0], self::$photinh[3][0]];
        $getDungThanCachCuc = self::getDungThanCachCuc($daivanmuoinam['thap_than'], $tutruthapthan,  $photinh_thapthan, $tinhan);

        // tinh cách nội tâm
        $tinhcanhnoitam = self::$tinhcachnoitam_bang1[$dayStem] ?? '';
        $tinhcachnguhanh = '';
        foreach ($dungthan as $index => $element) {
            if ($element > 50) {
                $tinhcachnguhanh .= self::$tinhcachnoitam_bang2[$index][0];
            } elseif ($element < 8) {
                $tinhcachnguhanh .= self::$tinhcachnoitam_bang2[$index][1];
            } else if ($element >= 20 && $element <= 50) {
                $tinhcachnguhanh .= self::$tinhcachnoitam_bang2[$index][2];
            }
        }
        $tinhcanhnoitam .= ' ' . $tinhcachnguhanh;
        if (array_values($photin['day'])[0] == 'Chính Tài') {
            $tinhcanhnoitam .= ' Nếu là nam mệnh thì là người rất thích ở nhà.';
        }

        if ($dayStem == 'Giáp') {
            if (self::$nhatchu > 20) {
                $tinhcanhnoitam .= ' Cảm thấy hòa hợp với cấp trên, người quản lý mình. Mong muốn theo đuổi lý tưởng.';
            }
            if ($dungthan[1] < 8) {
                $tinhcanhnoitam .= ' Ít cảm thấy vui vẻ.';
            }
        } else if ($dayStem == 'Ất') {
            if ($dungthan[1] < 8 && $yearStem != 'Giáp' && $monthStem != 'Giáp' && $hourStem != 'Giáp') {
                $tinhcanhnoitam .= ' Tính cách hướng nội, đa nghi.';
            }
            $tinhcanhnoitam .= ' Vui vẻ hợp với cấp dưới, nếu là phụ nữ sẽ là người yêu trẻ con.';
        } else if ($dayStem == 'Bính') {
            if (self::$nhatchu >= 20 && self::$nhatchu <= 50) {
                $tinhcanhnoitam .= ' Vui vẻ hòa hợp với cấp trên, người quản lý mình. Có lý tưởng to lớn.';
            }
            if (self::$nhatchu < 20) {
                $tinhcanhnoitam .= ' Tâm Tính hòa hợp với cha mẹ, trưởng bối. Mong muốn phát triển, lan tỏa.';
            }
        } else if ($dayStem == 'Đinh') {
            $tinhcanhnoitam .= ' Yêu thích tiền bạc và quyền hành.';
            if ($dungthan[0] < 50) {
                $tinhcanhnoitam .= ' Tâm tính hòa hợp với cha mẹ, trưởng bối.';
            } else if ($dungthan[0] >= 50) {
                $tinhcanhnoitam .= ' Không gần gũi với mẹ, trưởng bối. Nam mệnh hay được vợ giúp đỡ. Nữ mệnh thì hay được cha giúp đỡ hoặc dùng Tài Chính làm phương thức giải quyết vấn đề.';
            }

            if (self::$nhatchu < 20) {
                $tinhcanhnoitam .= ' Không được hòa hợp với cấp dưới, nữ mệnh thì khó gần trẻ con, áp lực trong việc nuôi dạy con cái.';
            }
        } else if ($dayStem == 'Mậu') {
            if (self::$nhatchu >= 20) {
                $tinhcanhnoitam .= ' Hòa hợp với cấp trên, cấp quản lý.';
            } else if (self::$nhatchu < 20) {
                $tinhcanhnoitam .= ' Tâm Tính hợp cha mẹ, trưởng bối.';
            }

            if ($dungthan[1] >= 20) {
                $tinhcanhnoitam .= ' Nam mệnh hay được vợ giúp đỡ. Nữ mệnh thì hay được cha giúp đỡ hoặc dùng Tài Chính làm phương thức giải quyết vấn đề.';
            }
        } else if ($dayStem == 'Kỷ') {
            $tinhcanhnoitam .= ' Tâm Tính hợp cha mẹ, trưởng bối.';
        } else if ($dayStem == 'Canh') {
            if (self::$nhatchu >= 20) {
                $tinhcanhnoitam .= ' Cảm thấy hòa hợp với cấp trên, người quản lý mình. Mục tiêu theo đuổi tiền tài.';
            }
        } else if ($dayStem == 'Tân') {
            if (self::$nhatchu < 20) {
                $tinhcanhnoitam .= ' Yêu thích đầu tư, mục tiêu khá rõ ràng, hòa hợp với cấp dưới, nữ mệnh thì yêu quý trẻ con.';
            }
        } else if ($dayStem == 'Nhâm') {
            $tinhcanhnoitam .= ' Có tâm lý khéo léo, giàu cảm xúc, dám nói dám làm, dễ thành công về mặt kỹ thuật và nghệ thuật.';
            if (self::$nhatchu < 8) {
                $tinhcanhnoitam .= ' Không được hòa hợp với cấp trên, nữ mệnh không hợp với chồng.';
            }
            if (self::$nhatchu >= 20) {
                $tinhcanhnoitam .= ' Hòa hợp với cấp trên, nhờ cấp trên mà đạt được phú quý, mệnh nữ nhờ được chồng.';
            }
        } else if ($dayStem == 'Quý') {
            if (self::$nhatchu < 8) {
                $tinhcanhnoitam .= ' Tâm tính hòa hợp với cha mẹ, trưởng bối.';
            }
            if (self::$nhatchu >= 20) {
                $tinhcanhnoitam .= ' Hòa hợp với cấp trên, nữ mệnh nhờ được chồng.';
            }
        }

        $TinhCachtykien = '';

        if (in_array(array_values($photin['year'])[0], ['Tỷ', 'Kiếp'])) {
            $TinhCachtykien .= ' Mệnh chủ có cảm giác an tâm khi ở quê cha đất tổ, có tâm hướng tới ông bà tổ tiên.';
        }

        if (in_array(array_values($photin['month'])[0], ['Tỷ', 'Kiếp'])) {
            $TinhCachtykien .= ' Mệnh chủ có cảm giác an toàn khi ở nhà bố mẹ hoặc anh chị.';
        }

        if (in_array(array_values($photin['day'])[0], ['Tỷ', 'Kiếp'])) {
            $TinhCachtykien .= ' Mệnh chủ có cảm giác an toàn và thích ở nhà, tâm hướng về gia đình, tuy nhiên bản thân cũng hay bị thị phi.';
        }

        if (in_array(array_values($photin['hour'])[0], ['Tỷ', 'Kiếp'])) {
            $TinhCachtykien .= ' Mệnh chủ có cảm giác an toàn khi ở xa nhà và thường xuyên ra ngoài ít khi ở nhà.';
        }
        $tinhcanhnoitam .= ' ' . $TinhCachtykien;

        //Tính cách thể hiện trong các mối quan hệ.
        $tinhcachthehientrongcacmoiquanhe = self::$tinhcachthehientrongcacmoiquanhe[array_values($photin['month'])[0]] ?? '';
        $phan_tram_an_tinh = collect($tinhan)->filter(function ($item) {
            return $item['sao'] == 'Ấn Tinh';
        })->first()['diem'] ?? 0;
        $phan_tram_quan_sat = collect($tinhan)->filter(function ($item) {
            return $item['sao'] == 'Quan Sát';
        })->first()['diem'] ?? 0;
        $phan_tram_tai_tinh = collect($tinhan)->filter(function ($item) {
            return $item['sao'] == 'Tài Tinh';
        })->first()['diem'] ?? 0;
        $phan_tram_thuc_thuong = collect($tinhan)->filter(function ($item) {
            return $item['sao'] == 'Thực Thương';
        })->first()['diem'] ?? 0;
        $phan_tram_ty_kiep = collect($tinhan)->filter(function ($item) {
            return $item['sao'] == 'Tỷ Kiếp';
        })->first()['diem'] ?? 0;
        $bankhidiachi = [];
        foreach ($photin as $pillar) {
            $bankhidiachi[] = array_values($pillar)[0];
        }
        $all_chutinh = array_values($chuTinh);
        if ($phan_tram_an_tinh > 8 && $phan_tram_an_tinh < 50 && self::$nhatchu <= 20 && (in_array('Chính Ấn', $all_chutinh) || in_array('Chính Ấn', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Có ý thức tự bảo vệ bản thân, chú ý đến cơ thể, sức khỏe, giàu tinh thần cống hiến, không ngại nhọc nhằn, ổn trọng nhưng không giỏi cạnh tranh. Chu đáo, có tư duy theo chiều sâu thích hợp nghiên cứu, đọc sách.';
        }
        if ($phan_tram_an_tinh < 8) {
            $tinhcachthehientrongcacmoiquanhe .= ' Chủ quan trong việc giữ gìn sức khỏe thể chất và tinh thần.';
        }
        if ($phan_tram_an_tinh > 50) {
            $tinhcachthehientrongcacmoiquanhe .= ' Suy nghĩ nhiều làm ít, tính ỷ lại mạnh mẽ, cố chấp với ý kiến của mình. Áp lực tâm lý, cảm xúc hay bồn chồn.';
        }

        if ($phan_tram_quan_sat > 8 && $phan_tram_quan_sat < 50 && self::$nhatchu >= 20 && (in_array('Quan', $all_chutinh) || in_array('Quan', $bankhidiachi)) && (!in_array('Sát', $bankhidiachi) && !in_array('Sát', $all_chutinh))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thông minh, kế hoạch rõ ràng, cẩn thận, dám nghĩ dám đi đầu, dễ được người khác tin phục.';
        }
        if ($phan_tram_quan_sat < 8) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thiếu quyết đoán, không tuân thủ theo nguyên tắc.';
        }
        if ($phan_tram_quan_sat >= 50 && self::$nhatchu < 20) {
            $tinhcachthehientrongcacmoiquanhe .= ' Hướng nội, nhút nhát, chịu nhiều áp lực.';
        }
        if ($phan_tram_tai_tinh > 8 && $phan_tram_tai_tinh < 50 && self::$nhatchu >= 20 && (in_array('Chính Tài', $all_chutinh) || in_array('Chính Tài', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Bản tính ôn hòa, tình thâm, lâu dài, không vội vàng cầu thành, không ảo tưởng, ý thức chủ động về kinh tế.';
        }
        if ($phan_tram_tai_tinh < 8) {
            $tinhcachthehientrongcacmoiquanhe .= ' Suy nghĩ thiếu thực tế.';
        }
        if ($phan_tram_tai_tinh >= 50 && (in_array('Chính Tài', $all_chutinh) || in_array('Chính Tài', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Quá quan tâm đến vật chất và niềm vui, tinh thần kém.';
        }

        if ($phan_tram_thuc_thuong > 8 && $phan_tram_thuc_thuong < 50 && self::$nhatchu >= 20 && (in_array('Thực', $all_chutinh) || in_array('Thực', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Ủng hộ tự do và bình đẳng, có tinh thần chống lại những việc tiêu cực, đa tài.';
        }
        if ($phan_tram_thuc_thuong < 8) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thiếu sự linh hoạt, đối nhân xử thế không khéo léo.';
        }
        if ($phan_tram_thuc_thuong >= 50) {
            $tinhcachthehientrongcacmoiquanhe .= ' Ham hưởng thụ, tùy ý không nghĩ tới tiến bộ.';
        }

        if (in_array('Thực', $bankhidiachi) && in_array('Thương', $bankhidiachi)) {
            $tinhcachthehientrongcacmoiquanhe .= ' Trong lòng suy nghĩ nhiều.';
        }

        if ($phan_tram_tai_tinh > 8 && $phan_tram_tai_tinh < 50 && self::$nhatchu >= 20 && (in_array('Thiên Tài', $all_chutinh) || in_array('Thiên Tài', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thông minh và linh hoạt, quyết đoán.';
        }
        if ($phan_tram_tai_tinh > 50 && (in_array('Thiên Tài', $all_chutinh) || in_array('Thiên Tài', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Dễ vì tiền bạc mà dẫn đến cực đoan, thích sự hào nhoáng.';
        }
        if ($phan_tram_ty_kiep > 8 && $phan_tram_ty_kiep < 50 && self::$nhatchu >= 20 && (in_array('Tỷ', $all_chutinh) || in_array('Tỷ', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thích kết bạn, bạn bè hòa thuận, làm việc chính đáng, có ý thức kinh tế, quý trọng những thứ đã đạt được. Bản thân thực tế và có tính thao tác cao, có kỹ thuật.';
        }
        if ($phan_tram_ty_kiep < 8) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thiếu tự tin, bản thân không cảm giác ổn định.';
        }
        if ($phan_tram_ty_kiep >= 50) {
            $tinhcachthehientrongcacmoiquanhe .= ' Bướng bỉnh, thiếu tinh thần đổi mới và tiên phong.';
        }
        if ($phan_tram_an_tinh > 8 && $phan_tram_an_tinh < 50 && self::$nhatchu >= 20 && (in_array('Thiên Ấn', $all_chutinh) || in_array('Thiên Ấn', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Tâm tính linh hoạt, nhạy cảm, có nhiều cách tiếp cận vấn đề khác nhau.';
        }
        if ($phan_tram_an_tinh >= 50 && (in_array('Thiên Ấn', $all_chutinh) || in_array('Thiên Ấn', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Cao ngạo, luôn muốn chiếm tiện nghi của người khác.';
        }
        if ($phan_tram_quan_sat > 8 &&  self::$nhatchu < 20 && (in_array('Sát', $all_chutinh) || in_array('Sát', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Bản thân chịu nhiều áp lực, dễ bị người khác khống chế hoặc quản lý quá mức.';
        }
        if ($phan_tram_quan_sat > 8 &&  self::$nhatchu >= 20 && (in_array('Sát', $all_chutinh) || in_array('Sát', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Bản thân muốn khống chế người khác, không ngại công kích, tranh đấu.';
        }

        if ($phan_tram_thuc_thuong > 8 && $phan_tram_thuc_thuong < 50 && self::$nhatchu >= 20 && (in_array('Thương', $all_chutinh) || in_array('Thương', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Tư duy nhanh nhẹn, ngột tính tốt, tài hoa hơn người, chí hướng lớn, biết nắm bắt thời cơ.';
        }
        if ($phan_tram_thuc_thuong > 50 && (in_array('Thương', $all_chutinh) || in_array('Thương', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Ngạo khí lớn, ngôn ngữ hà khắc, tùy ý, thích nổi bật. Mệnh nữ thường không phối hợp hoặc khắc chồng.';
        }
        if ($phan_tram_ty_kiep > 8 && $phan_tram_ty_kiep < 50 && self::$nhatchu < 20 && (in_array('Kiếp', $all_chutinh) || in_array('Kiếp', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thích kết giao bạn bè, là người nghĩa khí, được bạn bè giúp đỡ.';
        }
        if ($phan_tram_ty_kiep > 50 && (in_array('Kiếp', $all_chutinh) || in_array('Kiếp', $bankhidiachi))) {
            $tinhcachthehientrongcacmoiquanhe .= ' Thích tranh đấu, dễ mù quáng xúc động mà làm liều, thị phi nhiều.';
        }

        $diemdiachimenhcung = 26 - (self::$sochi[$monthBranch] + self::$sochi[$dayBranch]);
        if ($diemdiachimenhcung > 12) {
            $diemdiachimenhcung -= 12;
        }
        $diachimenhcung = array_search($diemdiachimenhcung, self::$sochi, true);
        $luandoantheomenhcung = self::$luandoanmenhcung[$diachimenhcung] ?? '';
        // sức khoẻ
        $suc_khoe = implode(' ', self::getSuckhoe([$_inputthiencan, $diachi_cantang], self::$nhatchu, $dungthan, $getMenh));


        // Lục thân
        $lucthan = implode(' ', self::getLucThan([$_inputthiencan, $diachi_cantang], self::$nhatchu, $dungthan, $getMenh));
        // sự nghiệp
        $arrcantang = [$chuTinh['hour'], array_values($photin['hour'])[0], 'Tỷ', array_values($photin['day'])[0], $chuTinh['month'], array_values($photin['month'])[0], $chuTinh['year'], array_values($photin['year'])[0]];
        $sunghiep = implode(' ', self::getSuNghiep([$_inputthiencan, $diachi_cantang], self::$nhatchu, $dungthan, $getMenh, $daivanmuoinam['can'], $daivanmuoinam['chi'], $arrcantang));

        return [
            'chu_tinh' => $chuTinh,
            'bat_tu' => $pillars,
            'can_tang' => $cantang,
            'pho_tinh' => array_map('array_values', $photin),
            'truong_sinh' => $truongSinh,
            'nap_am' => $napAm,
            'than_sat' => $thanSat,
            'tinh_an' => $tinhan,
            'dung_than_ky_than' => $getDungThanKyThan,
            'dung_than_cach_cuc' => $getDungThanCachCuc,
            'tinh_cach_noi_tam' => $tinhcanhnoitam,
            'tinh_cach_the_hien_trong_cac_moi_quan_he' => $tinhcachthehientrongcacmoiquanhe,
            'suc_khoe' => $suc_khoe,
            'luan_doan_theo_menh_cung' => $luandoantheomenhcung,
            'luc_than' => $lucthan,
            'su_nghiep' => $sunghiep,
        ];
    }

    public static function getSuckhoe($arrThienCanDiaChi, $nhatchu, $_dungthan, $_getMenh)
    {
        $result = [];
        $data = [
            'Tuổi thọ không cao do các hoạt động trong cuộc sống tiêu hao nhiều năng lượng.',
            'Cơ thể hay bị ốm đau bệnh tật.',
            'Cơ thể hay bị bệnh hoặc cơ thể có khiếm khuyết.',
            'Tuổi thọ không cao, tinh thần kém.',
            'Dễ mắc bệnh về mắt hoặc bệnh liên quan đến máu huyết như tim, thiếu máu.',
            'Gân cốt dễ bị thương.',
            'Cơ thể dễ có khiếm khuyết hoặc có bệnh mãn tính.',
            'Cơ thể thiếu sức sống, suy nhược.',
            'Dễ mắc bệnh phong thấp, xương khớp.',
            'Chức năng hệ miễn dịch kém, tâm trạng không tốt.',
            'Bản thân hay phải suy nghĩ nhiều, dễ mất ngủ, chú ý đến sức khỏe tinh thần.',
            'Bản thân hay bị ốm đau bệnh tật.',
            'Cơ thể dễ có những khối u.'
        ];

        // Xử lý Ấn Tinh
        for ($i = 0; $i < 5; $i++) {
            if ($_getMenh[$i] == 'Ấn Tinh') {
                if ($_dungthan[$i] > 8 && $_dungthan[$i] < 50 && $nhatchu <= 20 && in_array('Chính Ấn', $arrThienCanDiaChi)) {
                    $result[] = 'Có ý thức tự bảo vệ bản thân, chú ý đến cơ thể, sức khỏe.';
                }
                if ($_dungthan[$i] < 8) {
                    $result[] = 'Chủ quan trong việc giữ gìn sức khỏe thể chất và tinh thần.';
                }
                if ($_dungthan[$i] > 50) {
                    $result[] = 'Áp lực tâm lý, cảm xúc hay bồn chồn.';
                }
            }
        }

        // Các điều kiện sức khỏe
        if ($_dungthan[1] >= 50 && ($_dungthan[0] + $_dungthan[1]) >= 65) {
            $result[] = $data[0];
        }
        if ($_dungthan[4] >= 50 && ($_dungthan[4] + $_dungthan[3]) >= 65) {
            $result[] = $data[1];
        }
        if ($_dungthan[3] < 8 && $_dungthan[4] < 8) {
            $result[] = $data[2];
        }
        if ($_dungthan[0] < 8 && $_dungthan[1] < 8) {
            $result[] = $data[3];
        }
        if ($_dungthan[1] < 8) {
            $result[] = $data[4];
        }

        // Kiểm tra gân cốt
        $gan = [];
        for ($i = 0; $i < 4; $i++) {
            if (strpos('Dần,Tỵ,Thân', $arrThienCanDiaChi[1][$i]['diachi']) !== false) {
                $gan[$arrThienCanDiaChi[1][$i]['diachi']] = true;
            }
        }

        if (count($gan) >= 3) {
            $result[] = $data[5];
        }

        // Điều kiện phức tạp
        if (($_dungthan[3] < 8 && $_dungthan[4] > 50) ||
            ($_dungthan[3] < 8 && $_dungthan[2] > 50) ||
            ($_dungthan[4] < 8 && $_dungthan[0] > 50) ||
            ($_dungthan[4] < 8 && $_dungthan[2] > 50) ||
            ($_dungthan[0] < 8 && $_dungthan[1] > 50) ||
            ($_dungthan[0] < 8 && $_dungthan[3] > 50) ||
            ($_dungthan[1] < 8 && $_dungthan[2] > 50) ||
            ($_dungthan[1] < 8 && $_dungthan[4] > 50) ||
            ($_dungthan[2] < 8 && $_dungthan[3] > 50) ||
            ($_dungthan[2] < 8 && $_dungthan[0] > 50)
        ) {
            $result[] = $data[6];
        }

        // Xử lý bát tự
        $_inputthiencan = $arrThienCanDiaChi[0];
        $diachi_cantang = $arrThienCanDiaChi[1];
        $battu = [];
        $arrdiachi = [];

        for ($i = 0; $i < 4; $i++) {
            $battu[] = $_inputthiencan[$i] . ' ' . $diachi_cantang[$i]['diachi'];
            $arrdiachi[] = $diachi_cantang[$i]['diachi'];
        }

        $arrGiap = array_merge(
            $_inputthiencan,
            [
                $diachi_cantang[0]['cantang'][0],
                $diachi_cantang[1]['cantang'][0],
                $diachi_cantang[2]['cantang'][0],
                $diachi_cantang[3]['cantang'][0]
            ]
        );

        if ($_inputthiencan[2] == 'Ất' && !in_array('Giáp', $arrGiap) && $_dungthan[1] < 8) {
            $result[] = $data[7];
        }
        if (in_array('Ất', $arrGiap) && !in_array('Giáp', $arrGiap) && $_dungthan[1] < 8) {
            $result[] = $data[8];
        }
        if ($_dungthan[0] > 8 && $_dungthan[4] >= 50) {
            $result[] = $data[9];
        }
        if ($_dungthan[0] + $_dungthan[1] > 60) {
            $result[] = $data[10];
        }
        if ($_dungthan[3] + $_dungthan[4] > 65) {
            $result[] = $data[11];
        }
        if ($_dungthan[2] >= 50) {
            $result[] = $data[12];
        }

        // Kiểm tra các cặp xung khắc
        $flag = 0;
        for ($i = 0; $i < 4; $i++) {
            if (
                $arrdiachi[$i] == 'Dần' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Thân') || ($i < 3 && $arrdiachi[$i + 1] == 'Thân'))
            ) {
                $flag = 1;
            }
            if (
                $arrdiachi[$i] == 'Mão' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Dậu') || ($i < 3 && $arrdiachi[$i + 1] == 'Dậu'))
            ) {
                $flag = 1;
            }
            if (($_inputthiencan[$i] == 'Giáp' || $_inputthiencan[$i] == 'Ất') &&
                (($i > 0 && ($_inputthiencan[$i - 1] == 'Canh' || $_inputthiencan[$i - 1] == 'Tân')) ||
                    ($i < 3 && ($_inputthiencan[$i + 1] == 'Canh' || $_inputthiencan[$i + 1] == 'Tân')))
            ) {
                $flag = 1;
            }
        }

        if (in_array('Canh Dần', $battu) || in_array('Tân Mão', $battu) || $flag == 1) {
            $result[] = 'Chú ý chấn thương gân cốt hoặc các bệnh liên quan đến gan mật.';
        }

        // Kiểm tra flag_3
        $flag_3 = 0;
        if (self::checkCanhNhau('Bính', ['Nhâm', 'Quý'], $_inputthiencan, 1)) {
            $flag_3 = 1;
        }
        if (self::checkCanhNhau('Đinh', ['Nhâm', 'Quý'], $_inputthiencan, 1)) {
            $flag_3 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if (
                $arrdiachi[$i] == 'Tỵ' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Hợi') || ($i < 3 && $arrdiachi[$i + 1] == 'Hợi'))
            ) {
                $flag_3 = 1;
            }
            if (
                $arrdiachi[$i] == 'Ngọ' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Tý') || ($i < 3 && $arrdiachi[$i + 1] == 'Tý'))
            ) {
                $flag_3 = 1;
            }
        }

        if (in_array('Nhâm Ngọ', $battu) || in_array('Quý Tỵ', $battu) || $flag_3 == 1) {
            $result[] = 'Chú ý các bệnh liên quan đến tinh thần như giảm trí nhớ, suy nhược thần kinh hoặc các bệnh liên quan đến mắt hoặc máu huyết tim mạch.';
        }

        // Kiểm tra flag_4
        $flag_4 = 0;
        for ($i = 0; $i < 4; $i++) {
            if (
                $arrdiachi[$i] == 'Thìn' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Mão') || ($i < 3 && $arrdiachi[$i + 1] == 'Mão'))
            ) {
                $flag_4 = 1;
            }
            if (
                $arrdiachi[$i] == 'Dần' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Tuất') || ($i < 3 && $arrdiachi[$i + 1] == 'Tuất'))
            ) {
                $flag_4 = 1;
            }
            if (
                $arrdiachi[$i] == 'Mùi' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Mão') || ($i < 3 && $arrdiachi[$i + 1] == 'Mão'))
            ) {
                $flag_4 = 1;
            }
            if (
                $arrdiachi[$i] == 'Sửu' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Dần') || ($i < 3 && $arrdiachi[$i + 1] == 'Dần'))
            ) {
                $flag_4 = 1;
            }
            if (($_inputthiencan[$i] == 'Mậu' || $_inputthiencan[$i] == 'Kỷ') &&
                (($i > 0 && ($_inputthiencan[$i - 1] == 'Giáp' || $_inputthiencan[$i - 1] == 'Mộc')) ||
                    ($i < 3 && ($_inputthiencan[$i + 1] == 'Giáp' || $_inputthiencan[$i + 1] == 'Mộc')))
            ) {
                $flag_4 = 1;
            }
        }

        if (
            in_array('Giáp Tuất', $battu) || in_array('Giáp Thìn', $battu) ||
            in_array('Ất Mùi', $battu) || in_array('Ất Sửu', $battu) || $flag_4 == 1
        ) {
            $result[] = 'Chú ý các bệnh liên quan đến tiêu hóa, dạ dày và da.';
        }

        // Kiểm tra flag_5
        $flag_5 = 0;
        for ($i = 0; $i < 4; $i++) {
            if (
                $arrdiachi[$i] == 'Thân' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Tỵ') || ($i < 3 && $arrdiachi[$i + 1] == 'Tỵ'))
            ) {
                $flag_5 = 1;
            }
            if (
                $arrdiachi[$i] == 'Dậu' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Tỵ') || ($i < 3 && $arrdiachi[$i + 1] == 'Tỵ'))
            ) {
                $flag_5 = 1;
            }
            if (($_inputthiencan[$i] == 'Canh' || $_inputthiencan[$i] == 'Tân') &&
                (($i > 0 && ($_inputthiencan[$i - 1] == 'Bính' || $_inputthiencan[$i - 1] == 'Đinh')) ||
                    ($i < 3 && ($_inputthiencan[$i + 1] == 'Bính' || $_inputthiencan[$i + 1] == 'Đinh')))
            ) {
                $flag_5 = 1;
            }
        }

        if (in_array('Bính Thân', $battu) || in_array('Đinh Dậu', $battu) || $flag_5 == 1) {
            $result[] = 'Chú ý các bệnh liên quan đến hô hấp, xương, đại tràng.';
        }

        // Kiểm tra flag_6
        $flag_6 = 0;
        for ($i = 0; $i < 4; $i++) {
            if (
                $arrdiachi[$i] == 'Tý' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Sửu') || ($i < 3 && $arrdiachi[$i + 1] == 'Sửu'))
            ) {
                $flag_6 = 1;
            }
            if (
                $arrdiachi[$i] == 'Tý' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Mùi') || ($i < 3 && $arrdiachi[$i + 1] == 'Mùi'))
            ) {
                $flag_6 = 1;
            }
            if (
                $arrdiachi[$i] == 'Tý' &&
                (($i > 0 && $arrdiachi[$i - 1] == 'Thìn') || ($i < 3 && $arrdiachi[$i + 1] == 'Thìn'))
            ) {
                $flag_6 = 1;
            }
            if (($_inputthiencan[$i] == 'Nhâm' || $_inputthiencan[$i] == 'Quý') &&
                (($i > 0 && ($_inputthiencan[$i - 1] == 'Mậu' || $_inputthiencan[$i - 1] == 'Kỷ')) ||
                    ($i < 3 && ($_inputthiencan[$i + 1] == 'Mậu' || $_inputthiencan[$i + 1] == 'Kỷ')))
            ) {
                $flag_6 = 1;
            }
        }

        if (in_array('Mậu Tý', $battu) || in_array('Kỷ Hợi', $battu) || $flag_6 == 1) {
            $result[] = 'Chú ý các bệnh liên quan đến hệ bài tiết, thận, bàng quang.';
        }

        return $result;
    }

    public static function getSuckhoeV1($dungthan, $pillars)
    {
        $suc_khoe = '';
        if ($dungthan[1] >= 50 && ($dungthan[0] + $dungthan[1]) >= 65) {
            $suc_khoe .= ' Tuổi thọ không cao do các hoạt động trong cuộc sống tiêu hao nhiều năng lượng.';
        }
        if ($dungthan[4] >= 50 && ($dungthan[3] + $dungthan[4]) >= 65) {
            $suc_khoe .= ' Hay bị ốm đau bệnh tật.';
        }
        if ($dungthan[3] < 8 && $dungthan[4] < 8) {
            $suc_khoe .= ' Cơ thể hay bị bệnh hoặc cơ thể có khiếm khuyết.';
        }
        if ($dungthan[0] < 8 && $dungthan[1] < 8) {
            $suc_khoe .= ' Tuổi thọ không cao, tinh thần kém.';
        }
        if ($dungthan[1] < 8) {
            $suc_khoe .= ' Dễ mắc bệnh về mắt hoặc bệnh liên quan đến máu huyết như tim, thiếu máu.';
        }
        $nguyenmenhcuc = $bankhidiachi = array_merge(...array_values(array_map('array_values', $pillars)));
        if (in_array('Dần', $nguyenmenhcuc) && in_array('Tỵ', $nguyenmenhcuc) && in_array('Thân', $nguyenmenhcuc)) {
            $suc_khoe .= ' Gân cốt dễ bị thương.';
        }
        if ($dungthan[3] < 8 && $dungthan[4] > 50) {
            $suc_khoe .= ' Cơ thể dễ có khiếm khuyết hoặc có bệnh mãn tính.';
        }

        if (
            ($dungthan[3] < 8 && $dungthan[4] > 50)
            || ($dungthan[3] < 8 && $dungthan[1] > 50)
            || ($dungthan[4] < 8 && $dungthan[0] > 50)
            || ($dungthan[4] < 8 && $dungthan[2] > 50)
            || ($dungthan[0] < 8 && $dungthan[1] > 50)
            || ($dungthan[0] < 8 && $dungthan[3] > 50)
            || ($dungthan[1] < 8 && $dungthan[2] > 50)
            || ($dungthan[1] < 8 && $dungthan[4] > 50)
            || ($dungthan[2] < 8 && $dungthan[3] > 50)
            || ($dungthan[2] < 8 && $dungthan[0] > 50)
        ) {
            $suc_khoe .= ' Cơ thể dễ có khiếm khuyết hoặc có bệnh mãn tính.';
        }
        if ($dayStem == 'Ất' && $monthStem != 'Giáp' && $yearStem != 'Giáp' && $hourStem != 'Giáp' && !in_array('Giáp', $bankhidiachi) && $dungthan[1] < 8) {
            $suc_khoe .= ' Cơ thể thiếu sức sống, suy nhược.';
        }

        if (($dayStem == 'Ất' || $monthStem == 'Ất' || $yearStem == 'Ất' || $hourStem == 'Ất' || in_array('Ất', $bankhidiachi)) && ($dayStem != 'Giáp' && $monthStem != 'Giáp' && $yearStem != 'Giáp' && $hourStem != 'Giáp' && !in_array('Giáp', $bankhidiachi)) && $dungthan[1] < 8) {
            $suc_khoe .= 'Dễ mắc bệnh phong thấp, xương khớp.';
        }

        if ($dungthan[0] < 8 && $dungthan[4] >= 50) {
            $suc_khoe .= ' Chức năng hệ miễn dịch kém, tâm trạng không tốt.';
        }

        if (($dungthan[0] + $dungthan[1]) > 60) {
            $suc_khoe .= ' Bản thân hay phải suy nghĩ nhiều, dễ mất ngủ, chú ý đến sức khỏe tinh thần.';
        }

        if (($dungthan[3] + $dungthan[4]) > 60) {
            $suc_khoe .= ' Bản thân hay bị ốm đau bệnh tật.';
        }

        if ($dungthan[2] >= 50) {
            $suc_khoe .= ' Cơ thể dễ có những khối u.';
        }

        $check1 = false;
        $check2 = false;
        $check3 = false;
        $check4 = false;
        $check5 = false;

        foreach (array_values($pillars) as $key => $p) {
            if (($p['can'] == 'Giáp' || $p['can'] == 'Ất') && isset($pillars[$key - 1]['can']) && ($pillars[$key - 1]['can'] == 'Canh' || $pillars[$key - 1]['can'] == 'Tân')) {
                $check1 = true;
            } else if (($p['can'] == 'Giáp' || $p['can'] == 'Ất') && isset($pillars[$key + 1]['can']) && ($pillars[$key + 1]['can'] == 'Canh' || $pillars[$key + 1]['can'] == 'Tân')) {
                $check1 = true;
            } else if (($p['can'] == 'Canh' && $p['chi'] == 'Dần') || ($p['can'] == 'Tân' && $p['chi'] == 'Mão')) {
                $check1 = true;
            } else if ($p['chi'] == 'Dần' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Thân') {
                $check1 = true;
            } else if ($p['chi'] == 'Dần' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Thân') {
                $check1 = true;
            } else if ($p['chi'] == 'Mão' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Dậu') {
                $check1 = true;
            } else if ($p['chi'] == 'Mão' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Dậu') {
                $check1 = true;
            }

            if (($p['can'] == 'Bính' || $p['can'] == 'Đinh') && isset($pillars[$key - 1]['can']) && ($pillars[$key - 1]['can'] == 'Nhâm' || $pillars[$key - 1]['can'] == 'Quý')) {
                $check2 = true;
            } else if (($p['can'] == 'Bính' || $p['can'] == 'Đinh') && isset($pillars[$key + 1]['can']) && ($pillars[$key + 1]['can'] == 'Nhâm' || $pillars[$key + 1]['can'] == 'Quý')) {
                $check2 = true;
            } else if (($p['can'] == 'Nhâm' && $p['chi'] == 'Ngọ') || ($p['can'] == 'Quý' && $p['chi'] == 'Tỵ')) {
                $check2 = true;
            } else if ($p['chi'] == 'Tỵ' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Hợi') {
                $check2 = true;
            } else if ($p['chi'] == 'Tỵ' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Hợi') {
                $check2 = true;
            } else if ($p['chi'] == 'Ngọ' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Tý') {
                $check2 = true;
            } else if ($p['chi'] == 'Ngọ' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Tý') {
                $check2 = true;
            }

            // bảng này sai
            if (($p['can'] == 'Mậu' || $p['can'] == 'Kỷ') && isset($pillars[$key - 1]['can']) && ($pillars[$key - 1]['can'] == 'Giáp' || $pillars[$key - 1]['can'] == 'Quý')) {
                $check3 = true;
            } else if (($p['can'] == 'Mậu' || $p['can'] == 'Kỷ') && isset($pillars[$key + 1]['can']) && ($pillars[$key + 1]['can'] == 'Giáp' || $pillars[$key + 1]['can'] == 'Quý')) {
                $check3 = true;
            } else if (($p['can'] == 'Nhâm' && $p['chi'] == 'Ngọ') || ($p['can'] == 'Quý' && $p['chi'] == 'Tỵ')) {
                $check3 = true;
            } else if ($p['chi'] == 'Tỵ' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Hợi') {
                $check3 = true;
            } else if ($p['chi'] == 'Tỵ' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Hợi') {
                $check3 = true;
            } else if ($p['chi'] == 'Ngọ' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Tý') {
                $check3 = true;
            } else if ($p['chi'] == 'Ngọ' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Tý') {
                $check3 = true;
            }

            if (($p['can'] == 'Canh' || $p['can'] == 'Tân') && isset($pillars[$key - 1]['can']) && ($pillars[$key - 1]['can'] == 'Bính' || $pillars[$key - 1]['can'] == 'Đinh')) {
                $check4 = true;
            } else if (($p['can'] == 'Canh' || $p['can'] == 'Tân') && isset($pillars[$key + 1]['can']) && ($pillars[$key + 1]['can'] == 'Bính' || $pillars[$key + 1]['can'] == 'Đinh')) {
                $check4 = true;
            } else if (($p['can'] == 'Bính' && $p['chi'] == 'Thân') || ($p['can'] == 'Đinh' && $p['chi'] == 'Dậu')) {
                $check4 = true;
            } else if ($p['chi'] == 'Thân' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Tỵ') {
                $check4 = true;
            } else if ($p['chi'] == 'Thân' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Tỵ') {
                $check4 = true;
            } else if ($p['chi'] == 'Dậu' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Tỵ') {
                $check4 = true;
            } else if ($p['chi'] == 'Dậu' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Tỵ') {
                $check4 = true;
            }

            if (($p['can'] == 'Nhâm' || $p['can'] == 'Quý') && isset($pillars[$key - 1]['can']) && ($pillars[$key - 1]['can'] == 'Mậu' || $pillars[$key - 1]['can'] == 'Kỷ')) {
                $check4 = true;
            } else if (($p['can'] == 'Nhâm' || $p['can'] == 'Quý') && isset($pillars[$key + 1]['can']) && ($pillars[$key + 1]['can'] == 'Mậu' || $pillars[$key + 1]['can'] == 'Kỷ')) {
                $check4 = true;
            } else if (($p['can'] == 'Mậu' && $p['chi'] == 'Tý') || ($p['can'] == 'Kỷ' && $p['chi'] == 'Hợi')) {
                $check4 = true;
            } else if ($p['chi'] == 'Tý' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Sửu') {
                $check4 = true;
            } else if ($p['chi'] == 'Tý' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Sửu') {
                $check4 = true;
            } else if ($p['chi'] == 'Mùi' && isset($pillars[$key - 1]['chi']) && $pillars[$key - 1]['chi'] == 'Thìn') {
                $check4 = true;
            } else if ($p['chi'] == 'Mùi' && isset($pillars[$key + 1]['chi']) && $pillars[$key + 1]['chi'] == 'Thìn') {
                $check4 = true;
            }
        }
        if ($check1) {
            $suc_khoe .= ' Chú ý chấn thương gân cốt hoặc các bệnh liên quan đến gan mật.';
        }

        if ($check2) {
            $suc_khoe .= ' Chú ý các bệnh liên quan đến tinh thần như giảm trí nhớ, suy nhược thần kinh hoặc các bệnh liên quan đến mắt hoặc máu huyết tim mạch.';
        }

        if ($check3) {
            $suc_khoe .= ' Chú ý các bệnh liên quan đến tiêu hóa, dạ dày và da.';
        }

        if ($check4) {
            $suc_khoe .= ' Chú ý các bệnh liên quan đến hô hấp, xương, đại tràng.';
        }

        if ($check5) {
            $suc_khoe .= ' Chú ý các bệnh liên quan đến hệ bài tiết, thận, bàng quang.';
        }
    }

    public static function getSuNghiep($arrThienCanDiaChi, $nhatchu, $_dungthan, $_getMenh, $canDV, $chiDV, $arrcantang)
    {
        // echo 'getSuNghiep input', $arrThienCanDiaChi, $nhatchu, $arrcantang;
        $_inputthiencan = $arrThienCanDiaChi[0];
        $diachi_cantang = $arrThienCanDiaChi[1];
        $battu = [];
        $arrdiachi = [];
        $resutl = [];

        for ($i = 0; $i < 4; $i++) {
            $battu[] = $_inputthiencan[$i] . " " . $diachi_cantang[$i]['diachi'];
            $arrdiachi[] = $diachi_cantang[$i]['diachi'];
        }

        $arrGiap = array_merge(
            $_inputthiencan,
            $diachi_cantang[0]['cantang'],
            $diachi_cantang[1]['cantang'],
            $diachi_cantang[2]['cantang'],
            $diachi_cantang[3]['cantang']
        );

        $arrCheck = [$_inputthiencan[0], $_inputthiencan[1], $_inputthiencan[3]];
        $thiencanngay = $_inputthiencan[2];

        if (
            ($thiencanngay == 'Giáp' && in_array('Kỷ', $arrCheck)) ||
            ($thiencanngay == 'Bính' && in_array('Tân', $arrCheck)) ||
            ($thiencanngay == 'Mậu' && in_array('Quý', $arrCheck)) ||
            ($thiencanngay == 'Canh' && in_array('Ất', $arrCheck)) ||
            ($thiencanngay == 'Nhâm' && in_array('Đinh', $arrCheck))
        ) {
            $resutl[] = 'Là người yêu thích kiếm tiền, có duyên với tiền bạc, nếu là nam có duyên với người khác giới.';
        }

        if (
            ($thiencanngay == 'Ất' && in_array('Canh', $arrCheck)) ||
            ($thiencanngay == 'Đinh' && in_array('Nhâm', $arrCheck)) ||
            ($thiencanngay == 'Kỷ' && in_array('Giáp', $arrCheck)) ||
            ($thiencanngay == 'Tân' && in_array('Bính', $arrCheck)) ||
            ($thiencanngay == 'Quý' && in_array('Mậu', $arrCheck))
        ) {
            $resutl[] = 'Là người yêu thích quyền hành, dễ được người khác tín nhiệm, nếu là nữ có duyên với người khác giới.';
        }

        $data = [
            'Kinh tế, tài chính gặp nhiều bất lợi.',
            'Bản thân tập trung quá nhiều vào làm mà chưa xác định được mục tiêu cho bản thân. Trong quá trình học tập nghiên cứu ham học những cái mới nhưng khả năng đúc kết kiến thức hạn chế.',
            'Là người thông minh sáng dạ, ham học hỏi, ngộ tính cao, tư duy phát triển.',
            'Thích văn hóa truyền thống.',
            'Có tư duy quản lý hoặc kinh doanh.',
            'Có năng lực giác ngộ cao nhưng cần chú ý dễ học tập nghiên cứu đến mức mù quáng.',
            'Quá nguyên tắc chấp hành.',
            'Dễ gặp thị phi, chuyện tiêu cực, hay thay đổi.',
            'Thiếu chí tiến thủ trong công việc.',
            'Thiếu sự đam mê, nhiệt huyết cho công việc và cuộc sống.',
            'Phương thức kiếm tiền của bản thân dễ có sự thay đổi, khó lâu dài.',
            'Thiếu tầm nhìn xa trông rộng, xử sự thiếu bình tĩnh.',
            'Hành động lặp đi lặp lại, rụt rè, khả năng thích ứng với môi trường kém.',
            'Không ngừng phát triển bản thân, mong muốn phát triển công việc.',
            'Có chí tiến thủ, mạnh mẽ và thẳng thắn trong công việc và trách nhiệm với xã hội. Có đam mê rõ ràng trong cuộc sống và công việc.',
            'Ổn định gắn bó lâu dài trong công việc, biết cân đối các phương diện, dễ đạt được sự tin tưởng từ mọi người. Hãy làm những việc thiết thực trong cuộc sống và việc nhà.',
            'Ý thức công bằng trong mọi việc và có tinh thần tiến bộ nhanh chóng, đáp ứng được các công việc đòi hỏi tính logic và nguyên tắc cao.',
            'Khả năng thích ứng cao, giàu tưởng tượng, thích môi trường làm việc năng động.',
            'Trong Đại Vận hiện tại hay gặp chuyện tranh chấp cãi vã, nặng có thể liên quan đến kiện tụng pháp luật.',
            'Trong Đại Vận hiện tại bản thân dễ gặp tai họa bất ngờ.',
            'Trong Đại Vận hiện tại gặp nhiều chuyện tốt đẹp.',
            'Rời nhà đi nơi khác phát triển sự nghiệp.',
            'Xa quê cha đất tổ để gây dựng sự nghiệp.',
            'Khó có thể lập nghiệp được ở xa quê.',
            'Thường không có đơn vị làm việc cố định, hay chuyển việc.',
            'Có năng lực phối hợp các nguồn lực nhưng thiếu quyết đoán, thích hợp hơn với vị trí phó hơn trưởng.',
            'Có cấp trên hỗ trợ mới dễ thành tài.',
            'Thường nhờ đồng nghiệp hoặc bạn bè mình coi như anh cả hoặc cấp dưới giúp đỡ bản thân phát triển.'
        ];

        if ($_dungthan[3] < 8 && $_dungthan[4] < 8) {
            $resutl[] = $data[0];
        }

        if ($_dungthan[0] > 50 && $_dungthan[1] < 8) {
            $resutl[] = $data[1];
        }

        if ($_dungthan[0] >= 20 && $_dungthan[1] >= 20) {
            $resutl[] = $data[2];
        }

        if ($_dungthan[0] + $_dungthan[1] >= 40) {
            $resutl[] = $data[3];
        }

        if ($_dungthan[3] + $_dungthan[4] >= 40 && $_dungthan[3] + $_dungthan[4] <= 60) {
            $resutl[] = $data[4];
        }

        if ($_dungthan[1] > 50) {
            $resutl[] = $data[5];
        }

        if ($_dungthan[3] > 50) {
            $resutl[] = $data[6];
        }

        if ($_dungthan[4] > 50) {
            $resutl[] = $data[7];
        }

        if ($_dungthan[0] < 8) {
            $resutl[] = $data[8];
        }

        if ($_dungthan[1] < 8) {
            $resutl[] = $data[9];
        }

        if ($_dungthan[2] < 8 && $_dungthan[2] < 20) {
            $resutl[] = $data[10];
        }

        if ($_dungthan[3] < 8 && $_dungthan[2] < 20) {
            $resutl[] = $data[11];
        }

        if ($_dungthan[4] < 8) {
            $resutl[] = $data[12];
        }

        if ($_dungthan[0] >= 20 && $_dungthan[0] <= 50) {
            $resutl[] = $data[13];
        }

        if ($_dungthan[1] >= 20 && $_dungthan[1] <= 50) {
            $resutl[] = $data[14];
        }

        if ($_dungthan[2] >= 20 && $_dungthan[2] <= 50) {
            $resutl[] = $data[15];
        }

        if ($_dungthan[3] >= 20 && $_dungthan[3] <= 50) {
            $resutl[] = $data[16];
        }

        if ($_dungthan[4] >= 20 && $_dungthan[4] <= 50) {
            $resutl[] = $data[17];
        }

        if ($_dungthan[1] >= 50 && (strpos('Nhâm, Quý, Tý, Hợi', $canDV) !== false || strpos('Nhâm, Quý, Tý, Hợi', $chiDV) !== false)) {
            $resutl[] = $data[18];
        }

        if ($_dungthan[3] >= 50 && (strpos('Giáp, Ất, Dần, Mão', $canDV) !== false || strpos('Giáp, Ất, Dần, Mão', $chiDV) !== false)) {
            $resutl[] = $data[19];
        }

        if ($_dungthan[1] >= 20 && $_dungthan[1] <= 50 && (strpos('Nhâm Tý, Quý Hợi, Nhâm Thìn, Quý Sửu, Nhâm Thân, Quý Dậu', $canDV . " " . $chiDV) !== false)) {
            $resutl[] = $data[20];
        }

        if ($arrcantang[3] == 'Thương' && $arrcantang[4] == 'Chính') {
            $resutl[] = $data[21];
        }

        if ($arrcantang[6] == 'Thương' && $arrcantang[7] == 'Chính') {
            $resutl[] = $data[22];
        }

        if ($arrcantang[0] == 'Thương' && $arrcantang[1] == 'Chính') {
            $resutl[] = $data[23];
        }

        if ($_dungthan[1] >= 50 && ($_dungthan[0] + $_dungthan[1]) >= 65) {
            $resutl[] = $data[24];
        }

        if ($thiencanngay == 'Kỷ') {
            $resutl[] = $data[25];
        }

        if ($thiencanngay == 'Giáp' && $_dungthan[0] >= 20) {
            $resutl[] = $data[26];
        }

        if ($thiencanngay == 'Ất') {
            $resutl[] = $data[27];
        }

        $flag = 0;
        if (self::checkCanhNhau('Bính', ['Canh', 'Tân'], $_inputthiencan, 1)) {
            $flag = 1;
        }

        if (self::checkCanhNhau('Đinh', ['Canh', 'Tân'], $_inputthiencan, 1)) {
            $flag = 1;
        }

        if (self::checkCanhNhau('Tỵ', ['Thân', 'Dậu'], $arrdiachi)) {
            $flag = 1;
        }

        if (in_array('Bính Thân', $battu) || in_array('Đinh Dậu', $battu) || $flag == 1) {
            $resutl[] = 'Công việc bận rộn, tất bật.';
        }

        $flag_4 = 0;

        if (self::checkCanhNhau('Bính', ['Nhâm', 'Quý'], $_inputthiencan, 1)) {
            $flag_4 = 1;
        }

        if (self::checkCanhNhau('Đinh', ['Nhâm', 'Quý'], $_inputthiencan, 1)) {
            $flag_4 = 1;
        }

        if (self::checkCanhNhau('Tỵ', ['Hợi'], $arrdiachi)) {
            $flag_4 = 1;
        }

        if (self::checkCanhNhau('Ngọ', ['Tý'], $arrdiachi)) {
            $flag_4 = 1;
        }

        if (in_array('Bính Tý', $battu) || in_array('Đinh Hợi', $battu) || $flag_4 == 1) {
            $resutl[] = 'Quan tâm tới danh tiếng và lợi nhuận.';
        }

        $flag_5 = 0;
        if (self::checkCanhNhau('Bính', ['Mậu', 'Kỷ'], $_inputthiencan, 1)) {
            $flag_5 = 1;
        }

        if (self::checkCanhNhau('Đinh', ['Mậu', 'Kỷ'], $_inputthiencan, 1)) {
            $flag_5 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && $arrdiachi[$i - 1] == 'Tuất') || ($i < 3 && $arrdiachi[$i + 1] == 'Tuất'))) {
                $flag_5 = 1;
            }

            if ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && $arrdiachi[$i - 1] == 'Mùi') || ($i < 3 && $arrdiachi[$i + 1] == 'Mùi'))) {
                $flag_5 = 1;
            }

            if ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && $arrdiachi[$i - 1] == 'Sửu') || ($i < 3 && $arrdiachi[$i + 1] == 'Sửu'))) {
                $flag_5 = 1;
            }
        }

        if (in_array('Bính Thìn', $battu) || in_array('Đinh Sửu', $battu) || in_array('Bính Tuất', $battu) || in_array('Đinh Mùi', $battu) || $flag_5 == 1) {
            $resutl[] = 'Đảm đang, chăm làm việc nhà việc nội trợ, những việc thiết thực trong cuộc sống.';
        }

        $flag_6 = 0;
        $arrTHienCanCangTang = [$arrcantang[6], $arrcantang[4], $arrcantang[2], $arrcantang[0]];
        $arrDiachiCangTang = [$arrcantang[7], $arrcantang[5], $arrcantang[3], $arrcantang[1]];

        if (self::checkCanhNhau('Kiếp', ['Chính Quan', 'Thực'], $arrTHienCanCangTang, 1)) {
            $flag_6 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if ($arrTHienCanCangTang[$i] == 'Chính Quan' && $arrDiachiCangTang[$i] == 'Kiếp') {
                $flag_6 = 1;
            }

            if (($arrTHienCanCangTang[$i] == 'Kiếp' && $arrDiachiCangTang[$i] == 'Thực') || ($arrTHienCanCangTang[$i] == 'Thực' && $arrDiachiCangTang[$i] == 'Kiếp')) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Giáp' && ($arrdiachi[$i] == 'Mão' && (($i > 0 && $arrdiachi[$i - 1] == 'Dậu') || ($i < 3 && $arrdiachi[$i + 1] == 'Dậu')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Ất' && ($arrdiachi[$i] == 'Dần' && (($i > 0 && $arrdiachi[$i - 1] == 'Ngọ') || ($i < 3 && $arrdiachi[$i + 1] == 'Ngọ')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Ất' && ($arrdiachi[$i] == 'Dần' && (($i > 0 && $arrdiachi[$i - 1] == 'Thân') || ($i < 3 && $arrdiachi[$i + 1] == 'Thân')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && $arrdiachi[$i - 1] == 'Tuất') || ($i < 3 && $arrdiachi[$i + 1] == 'Tuất')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && $arrdiachi[$i - 1] == 'Tý') || ($i < 3 && $arrdiachi[$i + 1] == 'Tý')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Tỵ' && (($i > 0 && $arrdiachi[$i - 1] == 'Hợi') || ($i < 3 && $arrdiachi[$i + 1] == 'Hợi')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Mậu' && ($arrdiachi[$i] == 'Mùi' && (($i > 0 && $arrdiachi[$i - 1] == 'Mão') || ($i < 3 && $arrdiachi[$i + 1] == 'Mão')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Kỷ' && ($arrdiachi[$i] == 'Dậu' && (($i > 0 && $arrdiachi[$i - 1] == 'Thìn') || ($i < 3 && $arrdiachi[$i + 1] == 'Thìn')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Kỷ' && ($arrdiachi[$i] == 'Dậu' && (($i > 0 && $arrdiachi[$i - 1] == 'Tuất') || ($i < 3 && $arrdiachi[$i + 1] == 'Tuất')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Thân' && (($i > 0 && $arrdiachi[$i - 1] == 'Tý') || ($i < 3 && $arrdiachi[$i + 1] == 'Tý')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Thân' && (($i > 0 && $arrdiachi[$i - 1] == 'Tỵ') || ($i < 3 && $arrdiachi[$i + 1] == 'Tỵ')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Tý' && (($i > 0 && $arrdiachi[$i - 1] == 'Sửu') || ($i < 3 && $arrdiachi[$i + 1] == 'Sửu')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Tý' && (($i > 0 && $arrdiachi[$i - 1] == 'Mùi') || ($i < 3 && $arrdiachi[$i + 1] == 'Mùi')))) {
                $flag_6 = 1;
            }

            if ($_inputthiencan[2] == 'Quý' && ($arrdiachi[$i] == 'Hợi' && (($i > 0 && $arrdiachi[$i - 1] == 'Mão') || ($i < 3 && $arrdiachi[$i + 1] == 'Mão')))) {
                $flag_6 = 1;
            }
        }

        if ($flag_6 == 1) {
            $resutl[] = 'Có năng lực quản lý người khác, quản lý một nhóm người.';
        }

        $flag_7 = 0;
        if (self::checkCanhNhau('Thương', ['Chính Ấn', 'Chính Tài', 'Thiên Tài'], $arrTHienCanCangTang, 1)) {
            $flag_7 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if (($arrTHienCanCangTang[$i] == 'Thương' && ($arrDiachiCangTang[$i] == 'Chính Tài' || $arrDiachiCangTang[$i] == 'Thiên Tài')) || (($arrDiachiCangTang[$i] == 'Chính Tài' || $arrDiachiCangTang[$i] == 'Thiên Tài') && $arrDiachiCangTang[$i] == 'Quan')) {
                $flag_7 = 1;
            }

            if ($arrTHienCanCangTang[$i] == 'Chính Ấn' && $arrDiachiCangTang[$i] == 'Thương') {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Giáp' && ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && strpos('Tuất,Sửu,Mùi,Tý', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tuất,Sửu,Mùi,Tý', $arrdiachi[$i + 1]) !== false)))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Ất' && ($arrdiachi[$i] == 'Hợi' && (($i > 0 && $arrdiachi[$i - 1] == 'Tỵ') || ($i < 3 && $arrdiachi[$i + 1] == 'Tỵ')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Sửu' && (($i > 0 && $arrdiachi[$i - 1] == 'Dậu') || ($i < 3 && $arrdiachi[$i + 1] == 'Dậu')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Mão' && (($i > 0 && $arrdiachi[$i - 1] == 'Mùi') || ($i < 3 && $arrdiachi[$i + 1] == 'Mùi')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Thìn' && (($i > 0 && $arrdiachi[$i - 1] == 'Dậu') || ($i < 3 && $arrdiachi[$i + 1] == 'Dậu')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Tuất' && (($i > 0 && $arrdiachi[$i - 1] == 'Dậu') || ($i < 3 && $arrdiachi[$i + 1] == 'Dậu')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Tuất' && (($i > 0 && $arrdiachi[$i - 1] == 'Dần') || ($i < 3 && $arrdiachi[$i + 1] == 'Dần')))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Kỷ' && ($arrdiachi[$i] == 'Thân' && (($i > 0 && strpos('Tý,Hợi,Tỵ', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tý,Hợi,Tỵ', $arrdiachi[$i + 1]) !== false)))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Hợi' && (($i > 0 && strpos('Mão,Dần', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Mão,Dần', $arrdiachi[$i + 1]) !== false)))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Mão' && (($i > 0 && strpos('Ngọ,Dậu', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Ngọ,Dậu', $arrdiachi[$i + 1]) !== false)))) {
                $flag_7 = 1;
            }

            if ($_inputthiencan[2] == 'Quý' && ($arrdiachi[$i] == 'Dần' && (($i > 0 && strpos('Tỵ,Ngọ,Thân', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tỵ,Ngọ,Thân', $arrdiachi[$i + 1]) !== false)))) {
                $flag_7 = 1;
            }
        }

        if ($flag_7 == 1) {
            $resutl[] = 'Có kỹ thuật, kỹ nghệ thông qua nỗ lực bản thân rèn luyện được.';
        }

        $flag_8 = 0;
        if (self::checkCanhNhau('Sát', ['Chính Ấn', 'Thực'], $arrTHienCanCangTang, 1)) {
            $flag_8 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if ($arrTHienCanCangTang[$i] == 'Thực' && $arrDiachiCangTang[$i] == 'Sát') {
                $flag_8 = 1;
            }

            if (($arrTHienCanCangTang[$i] == 'Sát' && $arrDiachiCangTang[$i] == 'Chính Ấn') || ($arrTHienCanCangTang[$i] == 'Chính Ấn' && $arrDiachiCangTang[$i] == 'Sát')) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Giáp' && ($arrdiachi[$i] == 'Thân' && (($i > 0 && strpos('Tỵ,Tý', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tỵ,Tý', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Hợi' && (($i > 0 && strpos('Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Tý' && (($i > 0 && strpos('Sửu,Mùi', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Sửu,Mùi', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Mậu' && ($arrdiachi[$i] == 'Dần' && (($i > 0 && strpos('Thân,Ngọ', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Thân,Ngọ', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Kỷ' && ($arrdiachi[$i] == 'Mão' && (($i > 0 && strpos('Dậu', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dậu', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Canh' && ($arrdiachi[$i] == 'Tỵ' && (($i > 0 && strpos('Hợi', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Hợi', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && strpos('Tý,Tuất', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tý,Tuất', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Tuất' && (($i > 0 && strpos('Dần,Dậu', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dần,Dậu', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Thìn' && (($i > 0 && strpos('Dậu', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dậu', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }

            if ($_inputthiencan[2] == 'Quý' && ($arrdiachi[$i] == 'Mùi' && (($i > 0 && strpos('Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_8 = 1;
            }
        }

        if ($flag_8 == 1) {
            $resutl[] = 'Là người có năng lượng lớn, có năng lực sửa chữa, khống chế những điều tiêu cực, có thể làm nên đại sự.';
        }

        $flag_9 = 0;
        if (self::checkCanhNhau('Thiên Ấn', ['Tỷ', 'Chính Tài', 'Thiên Tài'], $arrTHienCanCangTang, 1)) {
            $flag_9 = 1;
        }

        for ($i = 0; $i < 4; $i++) {
            if (($arrTHienCanCangTang[$i] == 'Thiên Ấn' && $arrDiachiCangTang[$i] == 'Tỷ') || ($arrTHienCanCangTang[$i] == 'Tỷ' && $arrDiachiCangTang[$i] == 'Thiên Ấn')) {
                $flag_9 = 1;
            }

            if (($arrTHienCanCangTang[$i] == 'Chính Tài' || $arrTHienCanCangTang[$i] == 'Thiên Tài') && $arrDiachiCangTang[$i] == 'Thiên Ấn') {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Giáp' && ($arrdiachi[$i] == 'Hợi' && (($i > 0 && strpos('Dần', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dần', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Ất' && ($arrdiachi[$i] == 'Tý' && (($i > 0 && strpos('Tý ,Sửu,Mùi,Thìn,Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tý ,Sửu,Mùi,Thìn,Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Bính' && ($arrdiachi[$i] == 'Dần' && (($i > 0 && strpos('Thân,Tỵ', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Thân,Tỵ', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Đinh' && ($arrdiachi[$i] == 'Mão' && (($i > 0 && strpos('Dậu,Ngọ', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dậu,Ngọ', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Mậu' && ($arrdiachi[$i] == 'Tỵ' && (($i > 0 && strpos('Hợi', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Hợi', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Kỷ' && ($arrdiachi[$i] == 'Ngọ' && (($i > 0 && strpos('Tý,Sửu,Mùi', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tý,Sửu,Mùi', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Canh' && ($arrdiachi[$i] == 'Tuất' && (($i > 0 && strpos('Dần,Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dần,Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Canh' && ($arrdiachi[$i] == 'Thìn' && (($i > 0 && strpos('Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Sửu' && (($i > 0 && strpos('Dần,Dậu', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Dần,Dậu', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Tân' && ($arrdiachi[$i] == 'Mùi' && (($i > 0 && strpos('Mão', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Mão', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Nhâm' && ($arrdiachi[$i] == 'Thân' && (($i > 0 && strpos('Tỵ,Hợi', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tỵ,Hợi', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }

            if ($_inputthiencan[2] == 'Quý' && ($arrdiachi[$i] == 'Dậu' && (($i > 0 && strpos('Tỵ,Tý', $arrdiachi[$i - 1]) !== false) || ($i < 3 && strpos('Tỵ,Tý', $arrdiachi[$i + 1]) !== false)))) {
                $flag_9 = 1;
            }
        }

        if ($flag_9 == 1) {
            $resutl[] = 'Dựa vào trí thông minh, tài chí để làm nên sự nghiệp.';
        }

        // echo 'getSuNghiep output', $resutl;

        return $resutl;
    }

    private static function checkCanhNhau($target, $searchArray, $inputArray, $isThienCan = 0)
    {
        for ($i = 0; $i < count($inputArray); $i++) {
            if ($inputArray[$i] == $target) {
                if ($i > 0 && in_array($inputArray[$i - 1], $searchArray)) {
                    return true;
                }
                if ($i < count($inputArray) - 1 && in_array($inputArray[$i + 1], $searchArray)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getLucThan($arrThienCanDiaChi, $nhatchu, $dungthan, $getMenh)
    {
        $result = [];
        $data = [
            'Cha mẹ sức khỏe không tốt hoặc hay bất hòa cãi vã.',
            'Sức khỏe con cái không tốt hoặc hay bất hòa với con cái.',
            'Cô đơn, ít có sự gần gũi với người thân.',
            'Cha mẹ, trưởng bối, cấp trên hay gặp bất lợi. Bản thân không có chỗ dựa vững chắc, thiếu phúc khí, dễ bị quan phi miệng lưỡi.',
            'Bản Thân ít nhận được sự giúp đỡ từ cha mẹ, trưởng bối.',
            'Cha hoặc mẹ sức khỏe không tốt.',
            'Bản thân không được gần gũi với bố mẹ hoặc ông bà.',
            'Bản thân không được gần gũi với bố mẹ hoặc anh chị.',
            'Phần nhiều con cái lập nghiệp xa bố mẹ.',
            'Người phối ngẫu không hòa hợp với ông bà hoặc bố mẹ mình.',
            'Phần nhiều lập nghiệp sinh sống ở xa quê hương.',
            'Người phối ngẫu không được hòa hợp với cha mẹ hoặc anh chị em nhà mình.',
            'Người phối ngẫu thường xuyên ra ngoài hoặc làm việc xa nhà.',
            'Người phối ngẫu không được hòa hợp với con cái',
            'Người phối ngẫu hoặc con cái ít có thời gian ở nhà.',
            'Thường bị ràng buộc trong hôn nhân hoặc chuyện tình cảm.',
        ];

        $data2 = [
            'Cảm thấy hòa hợp với cấp trên, người quản lý mình. Mong muốn theo đuổi lý tưởng.',
            'Tính cách hướng nội, ít giao tiếp với mọi người.',
            'Vui vẻ hợp với cấp dưới, nếu là phụ nữ sẽ là người yêu trẻ con.',
            'Vui vẻ hòa hợp với cấp trên, người quản lý mình. Có lý tưởng lớn.',
            'Tâm Tính hợp với cha mẹ, trưởng bối.',
            'Tâm tính hợp với cha mẹ, trưởng bối.',
            'Không gần gũi với mẹ, trưởng bối. Nam mệnh hay được vợ giúp đỡ. Nữ mệnh thì hay được cha giúp đỡ hoặc nhiều vấn đề có thể dùng tài chính giải quyết được.',
            'Không được hòa hợp với cấp dưới, nữ mệnh thì khó gần trẻ con, áp lực trong việc nuôi dạy con cái.',
            'Hòa hợp với cấp trên, cấp quản lý.',
            'Tâm Tính hợp cha mẹ, trưởng bối.',
            'Nam mệnh hay được vợ giúp đỡ. Nữ mệnh thì hay được cha giúp đỡ hoặc nhiều vấn đề có thể dùng tài chính giải quyết được.',
            'Tâm Tính hợp cha mẹ, trưởng bối.',
            'Cảm thấy hòa hợp với cấp trên, người quản lý mình. Mục tiêu theo đuổi là tiền tài.',
            'Yêu thích đầu tư, mục tiêu khá rõ ràng, hòa hợp với cấp dưới, nữ mệnh thì yêu quý trẻ con.',
            'Dễ thành công về mặt kỹ thuật hoặc nghệ thuật.',
            'Không được hòa hợp với cấp trên, nữ mệnh không hợp với chồng.',
            'Hòa hợp với cấp trên, nhờ cấp trên mà đạt được phú quý, mệnh nữ nhờ được chồng.',
            'Tâm tính hợp với cha mẹ, trưởng bối.',
            'Hòa hợp với cấp trên, nữ mệnh nhờ được chồng.'
        ];

        $inputThienCan = $arrThienCanDiaChi[0];
        $diachi_cantang = $arrThienCanDiaChi[1];
        $battu = [];
        $arrdiachi = [];

        for ($i = 0; $i < 4; $i++) {
            $battu[] = $inputThienCan[$i] . ' ' . $diachi_cantang[$i]['diachi'];
            $arrdiachi[] = $diachi_cantang[$i]['diachi'];
        }

        $arrGiap = array_merge(
            $inputThienCan,
            $diachi_cantang[0]['cantang'],
            $diachi_cantang[1]['cantang'],
            $diachi_cantang[2]['cantang'],
            $diachi_cantang[3]['cantang']
        );

        // Các điều kiện chính
        if (
            !in_array($inputThienCan[0], ['Giáp', 'Ất', 'Bính', 'Đinh']) &&
            !in_array($inputThienCan[1], ['Giáp', 'Ất', 'Bính', 'Đinh']) &&
            !in_array($arrdiachi[0], ['Dần', 'Mão', 'Tỵ', 'Ngọ']) &&
            !in_array($arrdiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
        ) {
            $result[] = $data[0];
        }

        if (
            !in_array($inputThienCan[2], ['Canh', 'Tân', 'Nhâm', 'Quý']) &&
            !in_array($inputThienCan[3], ['Canh', 'Tân', 'Nhâm', 'Quý']) &&
            !in_array($arrdiachi[2], ['Thân', 'Dậu', 'Hợi', 'Tý']) &&
            !in_array($arrdiachi[3], ['Thân', 'Dậu', 'Hợi', 'Tý'])
        ) {
            $result[] = $data[1];
        }

        if ($dungthan[3] < 8 && $dungthan[4] < 8) $result[] = $data[2];
        if ($dungthan[0] < 8 && $dungthan[1] < 8) $result[] = $data[3];
        if (($dungthan[3] + $dungthan[4]) > 65)  $result[] = $data[4];
        if (($dungthan[0] + $dungthan[1]) > 65) $result[] = $data[5];

        // Các hàm check cần định nghĩa riêng
        if (self::checkThienCanXung($inputThienCan[2], $inputThienCan[0])) $result[] = $data[6];
        if (self::checkThienCanXung($inputThienCan[2], $inputThienCan[1])) $result[] = $data[7];
        if (self::checkThienCanXung($inputThienCan[2], $inputThienCan[3])) $result[] = $data[8];

        if (
            self::checkDiaChiHinh($diachi_cantang[2]['diachi'], $diachi_cantang[0]['diachi']) ||
            self::checkDiaChiHai($diachi_cantang[2]['diachi'], $diachi_cantang[0]['diachi']) ||
            self::checkDiaChiPha($diachi_cantang[2]['diachi'], $diachi_cantang[0]['diachi'])
        ) {
            $result[] = $data[9];
        }

        if (self::checkDiaChiXung($diachi_cantang[2]['diachi'], $diachi_cantang[0]['diachi'])) $result[] = $data[10];

        if (
            self::checkDiaChiHinh($diachi_cantang[2]['diachi'], $diachi_cantang[1]['diachi']) ||
            self::checkDiaChiHai($diachi_cantang[2]['diachi'], $diachi_cantang[1]['diachi']) ||
            self::checkDiaChiPha($diachi_cantang[2]['diachi'], $diachi_cantang[1]['diachi'])
        ) {
            $result[] = $data[11];
        }

        if (self::checkDiaChiXung($diachi_cantang[2]['diachi'], $diachi_cantang[1]['diachi'])) $result[] = $data[12];

        if (
            self::checkDiaChiHinh($diachi_cantang[2]['diachi'], $diachi_cantang[3]['diachi']) ||
            self::checkDiaChiHai($diachi_cantang[2]['diachi'], $diachi_cantang[3]['diachi']) ||
            self::checkDiaChiPha($diachi_cantang[2]['diachi'], $diachi_cantang[3]['diachi'])
        ) {
            $result[] = $data[13];
        }

        if (self::checkDiaChiXung($diachi_cantang[2]['diachi'], $diachi_cantang[3]['diachi'])) $result[] = $data[14];

        if (in_array($battu[2], ['Đinh Hợi', 'Mậu Tý', 'Tân Tỵ', 'Nhâm Ngọ'])) $result[] = $data[15];

        // Các điều kiện theo thiên can và nhật chủ
        $tc = $inputThienCan[2];
        if ($tc == 'Giáp' && $nhatchu >= 20) $result[] = $data2[0];

        if ($tc == 'Ất') {
            if (!in_array('Giáp', $arrGiap) && $getMenh[1] < 8) $result[] = $data2[1];
            if (($tc == 'Ất' || $tc == 'Giáp') && $getMenh[1] >= 8) $result[] = $data2[2];
        }

        if ($tc == 'Bính') {
            if ($nhatchu >= 20 && $nhatchu <= 25) $result[] = $data2[3];
            if ($nhatchu < 20) $result[] = $data2[4];
        }

        if ($tc == 'Đinh') {
            if ($getMenh[0] < 50) $result[] = $data2[5];
            else $result[] = $data2[6];
            if ($nhatchu < 20) $result[] = $data2[7];
        }

        if ($tc == 'Mậu') {
            if ($nhatchu >= 20) $result[] = $data2[8];
            else $result[] = $data2[9];
            if ($getMenh[1] >= 20) $result[] = $data2[10];
        }

        if ($tc == 'Kỷ') $result[] = $data2[11];
        if ($tc == 'Canh' && $nhatchu >= 20) $result[] = $data2[12];
        if ($tc == 'Tân' && $nhatchu <= 20) $result[] = $data2[13];
        if ($tc == 'Nhâm') {
            $result[] = $data2[14];
            if ($nhatchu < 8) $result[] = $data2[15];
            if ($nhatchu >= 20) $result[] = $data2[16];
        }
        if ($tc == 'Quý') {
            if ($nhatchu < 8) $result[] = $data2[17];
            if ($nhatchu >= 20) $result[] = $data2[18];
        }

        return $result;
    }

    private static function checkThienCanXung($menh1, $menh2)
    {
        if ($menh1 == $menh2) return false;
        $data = ['Canh,Giáp', 'Tân,Ất', 'Nhâm,Bính', 'Quý,Đinh', 'Giáp,Mậu', 'Mậu,Nhâm', 'Kỷ,Quý'];
        foreach ($data as $pair) {
            if (str_contains($pair, $menh1) && str_contains($pair, $menh2)) return true;
        }
        return false;
    }

    private static function checkDiaChiXung($menh1, $menh2)
    {
        if ($menh1 == $menh2) return false;
        $data = ['Dần,Thân', 'Tỵ,Hợi', 'Thìn,Tuất', 'Sửu,Mùi', 'Tý,Ngọ', 'Mão,Dậu'];
        foreach ($data as $pair) {
            if (str_contains($pair, $menh1) && str_contains($pair, $menh2)) return true;
        }
        return false;
    }

    private static function checkDiaChiHai($menh1, $menh2)
    {
        if ($menh1 == $menh2) return false;
        $data = ['Dậu,Tuất', 'Thân,Hợi', 'Mùi,Tý', 'Ngọ,Sửu', 'Tỵ,Hợi', 'Mão,Thìn'];
        foreach ($data as $pair) {
            if (str_contains($pair, $menh1) && str_contains($pair, $menh2)) return true;
        }
        return false;
    }

    private static function checkDiaChiPha($menh1, $menh2)
    {
        if ($menh1 == $menh2) return false;
        $data = ['Tý,Dậu', 'Ngọ,Mão', 'Thân,Tỵ', 'Dần,Hợi', 'Thìn,Sửu', 'Tuất,Mùi'];
        foreach ($data as $pair) {
            if (str_contains($pair, $menh1) && str_contains($pair, $menh2)) return true;
        }
        return false;
    }

    private static function checkDiaChiHinh($menh1, $menh2)
    {
        if ($menh1 == $menh2 && in_array($menh1, ['Thìn', 'Ngọ', 'Dậu', 'Hợi'])) return true;
        $data = ['Tý,Mão', 'Dần,Tỵ,Thân', 'Mùi,Tuất,Sửu', 'Thìn,Thìn', 'Ngọ,Ngọ', 'Dậu,Dậu', 'Hợi,Hợi'];
        foreach ($data as $pair) {
            if (str_contains($pair, $menh1) && str_contains($pair, $menh2) && $menh1 != $menh2) return true;
        }
        return false;
    }

    public static function isXungCan($can1, $can2)
    {
        $xungPairs = [
            'Giáp' => 'Canh',
            'Ất' => 'Tân',
            'Bính' => 'Nhâm',
            'Đinh' => 'Quý',
            'Mậu' => 'Giáp',
            'Kỷ' => 'Ất',
            'Canh' => 'Bính',
            'Tân' => 'Đinh',
            'Nhâm' => 'Mậu',
            'Quý' => 'Kỷ'
        ];

        return isset($xungPairs[$can1]) && $xungPairs[$can1] === $can2;
    }

    public static function getTinhMenh($thapthan)
    {
        if ($thapthan == 'Thiên Ấn' || $thapthan == 'Chính Ấn') {
            return 'Ấn Tinh';
        }
        if ($thapthan == 'Quan' || $thapthan == 'Sát') {
            return 'Quan Sát';
        }
        if ($thapthan == 'Tỷ' || $thapthan == 'Kiếp') {
            return 'Tỷ Kiếp';
        }
        if ($thapthan == 'Chính Tài' || $thapthan == 'Thiên Tài') {
            return 'Tài Tinh';
        }
        if ($thapthan == 'Thực' || $thapthan == 'Thương') {
            return 'Thực Thương';
        }
        return null; // hoặc có thể trả về giá trị mặc định khác
    }

    public static function checkIsQuansinhan($thapthantuttru)
    {
        $position = null;

        foreach (self::$quansinhan as $index => $item) {
            $countQS = 0;
            $countAT = 0;

            foreach ($thapthantuttru as $subIndex => $value) {
                if (isset($item[$subIndex])) {
                    if ($value == $item[$subIndex] && $value == 'Quan Sát') {
                        $countQS = 1;
                    }
                    if ($value == $item[$subIndex] && $value == 'Ấn Tinh') {
                        $countAT = 1;
                    }
                }

                $diem = $countQS + $countAT;
                if ($diem == 2) {
                    $position = $index;
                }
            }
        }

        return $position !== null ? true : false;
    }

    public static function checkIsThanSinhThuong($thapthantuttru)
    {
        $position = null;

        foreach (self::$thansinhthuong as $index => $item) {
            $countQS = 0;
            $countAT = 0;

            foreach ($thapthantuttru as $subIndex => $value) {
                if (isset($item[$subIndex])) {
                    if ($value == $item[$subIndex] && $value == 'Tài Tinh') {
                        $countQS = 1;
                    }
                    if ($value == $item[$subIndex] && $value == 'Thực Thương') {
                        $countAT = 1;
                    }
                }

                $diem = $countQS + $countAT;
                if ($diem == 2) {
                    $position = $index;
                }
            }
        }

        return $position !== null ? true : false;
    }

    public static function checkIssetQuanSat($thapthantuttru)
    {
        $flag = false;

        foreach ($thapthantuttru as $index => $item) {
            if ($item == 'Sát') {
                $flag = true;
            }
            if (($index == 0 || $index == 5) && ($item == 'Quan' || $item == 'Chính Quan')) {
                $flag = true;
            }
        }

        foreach (self::$cantangdaivan as $item) {
            if ($item == 'Sát') {
                $flag = true;
            }
        }

        return $flag;
    }

    public static function checkPhotinh($photinh)
    {
        $flag = false;

        foreach ($photinh as $item) {
            if (is_array($item) && count($item) > 1) {
                foreach ($item as $index => $thapthan) {
                    if ($index > 0 && $thapthan == 'Sát') {
                        $flag = true;
                    }
                }
            }
        }

        return $flag;
    }



    public static function checkIsThuongKhacQuan($thapthantuttru)
    {
        if (self::checkIssetQuanSat($thapthantuttru)) {
            return false;
        }
        if (self::checkPhotinh(self::$photinh)) {
            return false;
        }

        $countQS = 0;
        $countAT = 0;
        $position = null;
        $flag = false;

        foreach (self::$thuongkhacquan as $index => $item) {
            $countQS = 0;
            $countAT = 0;
            $flag = false;

            foreach ($thapthantuttru as $subIndex => $value) {
                if (isset($item[$subIndex])) {
                    if ($item[$subIndex] == 'Thương Quan' && $value == 'Thương') {
                        $countQS++;
                    }
                    if ($item[$subIndex] == 'Chính Quan' && $value == 'Quan') {
                        $countAT++;
                    }
                }
            }

            $diem = $countQS + $countAT;

            if ($countAT > $countQS) {
                // echo 'Chính Quan: ' . $index . PHP_EOL;
                $flag = true;
            } elseif ($countAT > 0 && $countQS > 0) {
                // echo 'Position: ' . $index . PHP_EOL;
                $position = $index;
                $flag = false;
            }
        }

        if ($flag) {
            return false;
        }

        return $position !== null ? true : false;
    }

    public static function getMenhDungthan($arr1, $arr2)
    {
        // Tìm giá trị nhỏ nhất trong arr1
        $minimum = min($arr1);
        $dungthan = '';
        $ky = '';

        // Duyệt mảng arr1 để tìm vị trí giá trị nhỏ nhất
        foreach ($arr1 as $_item) {
            if ($_item == $minimum) {
                // Duyệt mảng arr2 để tìm đối tượng có điểm trùng với giá trị nhỏ nhất
                foreach ($arr2 as $item) {
                    if (isset($item['diem']) && $item['diem'] == $minimum) {
                        $dungthan = $item['menh'];
                        $ky = self::menhKhac($dungthan); // gọi hàm menhKhac() tương tự JS
                    }
                }
            }
        }

        // Nếu chưa tìm được dungthân, lấy theo sao 'Tỷ Kiếp'
        if ($dungthan == '') {
            foreach ($arr2 as $item) {
                if (isset($item['sao']) && $item['sao'] == 'Tỷ Kiếp') {
                    $dungthan = $item['menh'];
                    $ky = self::menhKhac($dungthan);
                }
            }
        }

        // Trả về dạng mảng kết quả
        return [
            'dungthan' => $dungthan,
            'ky' => $ky
        ];
    }


    public static function getDungThanCachCuc($thapthandaivan, $thapthantutru, $thapthanphotinh, $tinhan)
    {
        // Tạo mảng thapthantuttru và thapthannguyenban
        $thapthantuttru = [
            self::getTinhMenh($thapthandaivan[0]),
            self::getTinhMenh($thapthantutru[0]),
            self::getTinhMenh($thapthantutru[1]),
            self::getTinhMenh($thapthantutru[2]),
            self::getTinhMenh($thapthantutru[3]),
            self::getTinhMenh($thapthandaivan[1]),
            self::getTinhMenh($thapthanphotinh[0]),
            self::getTinhMenh($thapthanphotinh[1]),
            self::getTinhMenh($thapthanphotinh[2]),
            self::getTinhMenh($thapthanphotinh[3])
        ];

        $thapthannguyenban = [
            $thapthandaivan[0],
            $thapthantutru[0],
            $thapthantutru[1],
            $thapthantutru[2],
            $thapthantutru[3],
            $thapthandaivan[1],
            $thapthanphotinh[0],
            $thapthanphotinh[1],
            $thapthanphotinh[2],
            $thapthanphotinh[3]
        ];

        $checkIsQuansinhan = self::checkIsQuansinhan($thapthantuttru);
        $checkIsThanSinhThuong = self::checkIsThanSinhThuong($thapthantuttru);
        $checkIsThuongKhacQuan = self::checkIsThuongKhacQuan($thapthannguyenban);

        $An = 0;
        $Ty = 0;
        $Quan = 0;
        $Tai = 0;
        $Thuong = 0;
        $text1 = '';
        $text2 = '';
        $text3 = '';
        $arrMenh = ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'];

        $dungthan = [];
        $hythan = [];
        $ky = [];
        $saokythan = [];
        $saohythan = [];

        // Gán điểm sao
        foreach ($tinhan as $item) {
            switch ($item['sao']) {
                case 'Ấn Tinh':
                    $An = $item['diem'];
                    break;
                case 'Tỷ Kiếp':
                    $Ty = $item['diem'];
                    break;
                case 'Thực Thương':
                    $Thuong = $item['diem'];
                    break;
                case 'Tài Tinh':
                    $Tai = $item['diem'];
                    break;
                case 'Quan Sát':
                    $Quan = $item['diem'];
                    break;
            }
        }

        // --- Trường hợp Quan sinh Ấn ---
        if ($checkIsQuansinhan) {
            $result = self::getMenhDungthan([self::$nhatchu, $An, $Quan], $tinhan);
            $dungthan[] = $result['dungthan'];
            $ky[] = $result['ky'];
            $saokythan[] = self::$Hythan[array_search(trim($result['ky']), $arrMenh)];
            $saohythan[] = self::$Hythan[array_search(trim($result['dungthan']), $arrMenh)];
        }

        // --- Trường hợp Thân sinh Thương ---
        if ($checkIsThanSinhThuong) {
            $result = self::getMenhDungthan([self::$nhatchu, $Thuong, $Tai], $tinhan);
            $dungthan[] = $result['dungthan'];
            $ky[] = $result['ky'];
            $saokythan[] = self::$Hythan[array_search(trim($result['ky']), $arrMenh)];
            $saohythan[] = self::$Hythan[array_search(trim($result['dungthan']), $arrMenh)];
        }

        // --- Trường hợp Thương khắc Quan ---
        if ($checkIsThuongKhacQuan) {
            $_ky = [];
            $_saokythan = [];
            $_saohythan = [];
            $_dungthan = [];

            if (self::$nhatchu >= 20) {
                foreach ($tinhan as $item) {
                    if ($item['sao'] === 'Thực Thương') {
                        $_dungthan[] = $item['menh'];
                        $_saohythan[] = self::$Hythan[array_search($item['menh'], $arrMenh)];
                    }
                    if (in_array($item['sao'], ['Tài Tinh', 'Quan Sát'])) {
                        $_ky[] = $item['menh'];
                        $_saokythan[] = self::$Hythan[array_search($item['menh'], $arrMenh)];
                    }
                }
                $text3 = 'Thân vượng';
            } else {
                foreach ($tinhan as $item) {
                    if (in_array($item['sao'], ['Tỷ Kiếp', 'Thực Thương'])) {
                        $_dungthan[] = $item['menh'];
                        $_saohythan[] = self::$Hythan[array_search($item['menh'], $arrMenh)];
                    }
                    if (in_array($item['sao'], ['Tài Tinh', 'Quan Sát'])) {
                        $_ky[] = $item['menh'];
                        $_saokythan[] = self::$Hythan[array_search($item['menh'], $arrMenh)];
                    }
                }
                $text3 = 'Thân nhược';
            }

            $ky[] = implode(',', $_ky);
            $dungthan[] = implode(',', $_dungthan);
            $saokythan[] = implode(',', $_saokythan);
            $saohythan[] = implode(',', $_saohythan);
        }

        return [
            'dungthan' => $dungthan,
            'hythan' => $hythan,
            'ky' => $ky,
            'quansinhan' => $checkIsQuansinhan,
            'thansinhthuong' => $checkIsThanSinhThuong,
            'thuongkhacquan' => $checkIsThuongKhacQuan,
            'text1' => $text1,
            'text2' => $text2,
            'text3' => $text3,
            'saokythan' => $saokythan,
            'saohythan' => $saohythan
        ];
    }

    public static function sumarr($arr)
    {
        $total = 0;
        foreach ($arr as $item) {
            $total += $item;
        }
        return $total;
    }


    public static function tinhNhatChu()
    {

        $photinh = self::$photinh;
        $arrNC =  self::$arrNC;
        $Diemthiencaniachi = self::$Diemthiencaniachi;
        // Điểm cơ bản
        $NC = $Diemthiencaniachi[0][2];

        // --- Thêm điểm theo các điều kiện ---
        if (self::$chuTinh['month'] == 'Chính Ấn' && $arrNC[1] != 1) {
            $NC += $Diemthiencaniachi[0][1] * 0.3;
        }
        if (self::$chuTinh['hour'] == 'Chính Ấn' && $arrNC[3] != 1) {
            $NC += $Diemthiencaniachi[0][3] * 0.3;
        }
        if (self::$chuTinh['month'] == 'Thiên Ấn' && $arrNC[1] != 1) {
            $NC += $Diemthiencaniachi[0][1] * 0.2;
        }
        if (self::$chuTinh['hour'] == 'Thiên Ấn' && $arrNC[3] != 1) {
            $NC += $Diemthiencaniachi[0][3] * 0.2;
        }
        if (self::$chuTinh['year'] == 'Chính Ấn' && $arrNC[0] != 1) {
            $NC += $Diemthiencaniachi[0][0] * 0.15;
        }
        if (self::$chuTinh['year'] == 'Thiên Ấn' && $arrNC[0] != 1) {
            $NC += $Diemthiencaniachi[0][0] * 0.10;
        }

        if (
            in_array(self::$chuTinh['month'], ['Kiếp', 'Tỷ', 'Tỉ'])
            || $arrNC[1] == 1
        ) {
            $NC += $Diemthiencaniachi[0][1];
        }
        if (
            in_array(self::$chuTinh['hour'], ['Kiếp', 'Tỷ', 'Tỉ'])
            || $arrNC[3] == 1
        ) {
            $NC += $Diemthiencaniachi[0][3];
        }
        if (
            in_array(self::$chuTinh['year'], ['Kiếp', 'Tỷ', 'Tỉ'])
            || $arrNC[0] == 1
        ) {
            $NC += $Diemthiencaniachi[0][0] * 0.5;
        }

        // --- Can tàng ---
        $cangtangthang = $photinh[1] ?? [];
        $cangtangngay  = $photinh[2] ?? [];
        $cangtangnam   = $photinh[0] ?? [];
        $cangtanggio   = $photinh[3] ?? [];

        // Tháng
        foreach ($cangtangthang as $index => $item) {
            if (
                (in_array($item, ['Kiếp', 'Tỷ', 'Tỉ']) || $arrNC[5] == 1)
                && $arrNC[5] != -1
            ) {
                $point = $Diemthiencaniachi[1][2][1]['realPoint'][$index] ?? 0;
                $NC += $point;
            }
        }

        // Ngày
        foreach ($cangtangngay as $index => $item) {
            if (
                (in_array($item, ['Kiếp', 'Tỷ', 'Tỉ']) || $arrNC[6] == 1)
                && $arrNC[6] != -1
            ) {
                $point = $Diemthiencaniachi[1][2][2]['realPoint'][$index] ?? 0;
                $NC += $point;
            }
        }

        // Năm
        foreach ($cangtangnam as $index => $item) {
            $point = $Diemthiencaniachi[1][2][0]['realPoint'][$index] ?? 0;
            if (
                (in_array($item, ['Kiếp', 'Tỷ', 'Tỉ']) || $arrNC[4] == 1)
                && $arrNC[4] != -1
            ) {
                $NC += $point * 0.6;
            }
        }

        // Giờ
        foreach ($cangtanggio as $index => $item) {
            if (
                (in_array($item, ['Kiếp', 'Tỷ', 'Tỉ']) || $arrNC[7] == 1)
                && $arrNC[7] != -1
            ) {
                $ratio = 0.8;
                if (in_array(self::$chuTinh['hour'], ['Kiếp', 'Tỷ', 'Tỉ'])) {
                    $ratio = 1;
                }
                $point = $Diemthiencaniachi[1][2][3]['realPoint'][$index] ?? 0;
                $NC += $point * $ratio;
            }
        }

        return $NC;
    }
    protected static $nhatchu = 0;

    public static function getDungkyThan()
    {
        // global $nguyet_tru, $photinh, $ratioNguhanh, $Hythan;

        // Tổng điểm ngũ hành
        $tongdiem = self::sumarr(self::$DiemNguHanh);
        $tinhNhatChu = self::tinhNhatChu();
        $NC = round(($tinhNhatChu / $tongdiem) * 100, 1);

        $cocan = false;
        // Kiểm tra có căn hay không
        foreach (self::$photinh as $item) {
            if (in_array('Tỷ', $item) || in_array('Tỉ', $item) || in_array('Kiếp', $item)) {
                $cocan = true;
                break;
            }
        }
        $menh = self::$Menhchu;
        $arrMenh = ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'];
        $dungthan = '';
        $kythan = '';
        $hythan = '';
        $menhchu = '';
        $indexMenh = 0;
        $maxPoint = self::$DiemNguHanh[0];
        $indexMenhChu = array_search($menh, $arrMenh);
        $ratioMenhchu = $NC;
        self::$nhatchu = $ratioMenhchu;
        $menhnguhanh = $arrMenh[$indexMenhChu];
        // Tìm hành có điểm lớn nhất
        foreach (self::$DiemNguHanh as $index => $item) {
            if ($maxPoint < $item) {
                $maxPoint = $item;
                $indexMenh = $index;
            }
        }

        // --- Các điều kiện chính ---
        if ($NC < 8 && !$cocan) {
            $dungthan = $arrMenh[$indexMenh] . ', ' . self::menhSinh($arrMenh[$indexMenh]);
            $kythan = self::menhKhac($arrMenh[$indexMenh]) . ', ' . self::bikhac($arrMenh[$indexMenh]);
            $hythan = self::$Hythan[$indexMenh] . ', ' . self::$Hythan[array_search(self::menhSinh($arrMenh[$indexMenh]), $arrMenh)];
            $menhchu = 'nhược cực';
            $saokythan = self::$Hythan[array_search(self::menhKhac($arrMenh[$indexMenh]), $arrMenh)] . ', ' .
                self::$Hythan[array_search(self::bikhac($arrMenh[$indexMenh]), $arrMenh)];
        }

        if ($NC < 8 && $cocan) {
            $dungthan = self::menhKhac($menh);
            $hythan = self::$Hythan[array_search($dungthan, $arrMenh)];
            $dungthan2 = self::sinhMenh($menh);
            $dungthan .= ', ' . $dungthan2;
            $hythan .= ', ' . self::$Hythan[array_search($dungthan2, $arrMenh)];
            $kythan = self::menhSinh($menh) . ', ' . $menh;
            $menhchu = 'quá nhược';
            $saokythan = self::$Hythan[array_search(self::sinhMenh($menh), $arrMenh)] . ', ' .
                self::$Hythan[array_search($menh, $arrMenh)];
        }

        if ($NC >= 8 && $NC <= 19) {
            $indexAnTinh = $indexMenhChu - 1;
            if ($indexAnTinh < 0) $indexAnTinh = 4;

            if (self::$ratioNguhanh[$indexMenhChu] < 50) {
                $dungthan = self::menhSinh($menh) . ', ' . $menh;
                $hythan = self::$Hythan[array_search(self::menhSinh($menh), $arrMenh)] . ', ' . self::$Hythan[array_search($menh, $arrMenh)];
                $kythan = self::menhKhac($menh) . ', ' . self::bikhac($menh);
                $saokythan = self::$Hythan[array_search(self::menhKhac($menh), $arrMenh)] . ', ' . self::$Hythan[array_search(self::bikhac($menh), $arrMenh)];
            } else {
                $dungthan = $menh;
                $hythan = self::$Hythan[array_search($menh, $arrMenh)];
                $kythan = self::menhKhac($menh) . ', ' . self::bikhac($menh);
                $saokythan = self::$Hythan[array_search(self::menhKhac($menh), $arrMenh)] . ', ' . self::$Hythan[array_search(self::bikhac($menh), $arrMenh)];
            }
            $menhchu = 'nhược';
        }

        if ($NC > 19 && $NC < 21) {
            $indexMenhNext = $indexMenh + 1;
            if ($indexMenhNext >= 5) $indexMenhNext = 0;
            $dungthan = $arrMenh[$indexMenhNext];
            $hythan = self::$Hythan[$indexMenhNext];
            $kythan = $arrMenh[$indexMenh];
            $menhchu = 'bình hoà';
            $saokythan = self::$Hythan[$indexMenh];
        }

        if ($NC >= 21 && $NC <= 50) {
            $dungthan = self::menhKhac($menh) . ', ' . self::bikhac($menh) . ', ' . self::sinhMenh($menh);
            $hythan = self::$Hythan[array_search(self::menhKhac($menh), $arrMenh)] . ', ' .
                self::$Hythan[array_search(self::bikhac($menh), $arrMenh)] . ', ' .
                self::$Hythan[array_search(self::sinhMenh($menh), $arrMenh)];
            $kythan = self::menhSinh($menh) . ', ' . $menh;
            $menhchu = 'vượng';
            $saokythan = self::$Hythan[array_search(self::menhSinh($menh), $arrMenh)] . ', ' . self::$Hythan[array_search($menh, $arrMenh)];
        }

        if ($NC > 50 && $NC <= 80) {
            $tiet = self::sinhMenh($menh);
            $hao = self::bikhac($menh);
            $indexTiet = array_search($tiet, $arrMenh);
            $indexHao = array_search($hao, $arrMenh);

            if (self::$ratioNguhanh[$indexTiet] > self::$ratioNguhanh[$indexHao]) {
                $dungthan = $tiet;
                $hythan = self::$Hythan[$indexTiet];
            } else {
                $dungthan = $hao;
                $hythan = self::$Hythan[$indexHao];
            }

            $kythan = self::menhSinh($menh) . ', ' . $menh . ', ' . self::menhKhac($menh);
            $saokythan = self::$Hythan[array_search(self::menhSinh($menh), $arrMenh)] . ', ' .
                self::$Hythan[array_search($menh, $arrMenh)] . ', ' .
                self::$Hythan[array_search(self::menhKhac($menh), $arrMenh)];
            $menhchu = 'quá vượng';
        }

        if ($NC > 80) {
            $dungthan = "$menh, " . self::menhSinh($menh);
            $hythan = self::$Hythan[array_search($menh, $arrMenh)] . ', ' . self::$Hythan[array_search(self::menhSinh($menh), $arrMenh)];
            $indexTiet = array_search(self::sinhMenh($menh), $arrMenh);
            if (self::$ratioNguhanh[$indexTiet] > 8) {
                $dungthan .= ', ' . self::sinhMenh($menh);
                $hythan .= ', ' . self::$Hythan[$indexTiet];
            }
            $kythan = self::bikhac($menh) . ', ' . self::menhKhac($menh);
            $saokythan = self::$Hythan[array_search(self::bikhac($menh), $arrMenh)] . ', ' . self::$Hythan[array_search(self::menhKhac($menh), $arrMenh)];
            $menhchu = 'vượng cực';
        }

        // --- Mùa sinh ---
        $thangsinh = self::$battu['month']['chi'];
        $muasinh = '';
        if (strpos('Dần, Mão, Thìn', $thangsinh) !== false) $muasinh = 'xuân';
        if (strpos('Tỵ, Ngọ, Mùi', $thangsinh) !== false) $muasinh = 'hè';
        if (strpos('Thân, Dậu, Tuất', $thangsinh) !== false) $muasinh = 'thu';
        if (strpos('Hợi, Tý, Sửu', $thangsinh) !== false) $muasinh = 'đông';

        // --- Nhiệt độ (Điều hậu) ---
        $nangluongHoa = self::$ratioNguhanh[1];
        $nangluongThuy = self::$ratioNguhanh[4];
        $chenhHoaThuy = abs($nangluongHoa - $nangluongThuy);
        $dungthandieuhau = '';
        $kythandieuhau = '';
        $textNangluong = 'nóng lạnh bình hòa. Không cần Dụng Thần Điều hậu';

        if ($muasinh == 'hè' && $nangluongHoa > 21 && $nangluongThuy < 19 && $chenhHoaThuy >= 10) {
            $dungthandieuhau = 'Thủy';
            $kythandieuhau = 'Hỏa';
            $textNangluong = 'quá nóng';
        }
        if ($muasinh == 'đông' && $nangluongThuy > 21 && $nangluongHoa < 19 && $chenhHoaThuy >= 10) {
            $dungthandieuhau = 'Hỏa';
            $kythandieuhau = 'Thủy';
            $textNangluong = 'quá lạnh';
        }

        return [
            'menh_chu' => $menhchu,
            'mua_sinh' => $muasinh,
            'dung_than' => $dungthan,
            'hy_than' => $hythan,
            'ky_than' => $kythan,
            'nang_luong_hoa' => $nangluongHoa,
            'nang_luong_thuy' => $nangluongThuy,
            'text_nang_luong' => $textNangluong,
            'dung_thanh_dieu_hau' => $dungthandieuhau,
            'ky_thanh_dieu_hau' => $kythandieuhau,
            'ratio_menh_chu' => $ratioMenhchu,
            'menh_nguhanh' => $menhnguhanh,
            'co_can' => $cocan,
            'sao_ky_than' => $saokythan
        ];
    }

    public static function getMenh($can)
    {
        // Xác định mệnh theo thiên can
        if (strpos('Giáp Ất', $can) !== false) {
            return 'Mộc';
        }
        if (strpos('Bính Đinh', $can) !== false) {
            return 'Hỏa';
        }
        if (strpos('Canh Tân', $can) !== false) {
            return 'Kim';
        }
        if (strpos('Mậu Kỷ', $can) !== false) {
            return 'Thổ';
        }
        return 'Thủy';
    }
    public static function getMenhThuong($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thổ';
        if ($menh == 'Kim') return 'Thủy';
        if ($menh == 'Thủy') return 'Mộc';
        if ($menh == 'Mộc') return 'Hỏa';
        if ($menh == 'Thổ') return 'Kim';
        return null;
    }

    public static function bikhac($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Kim';
        if ($menh == 'Kim') return 'Mộc';
        if ($menh == 'Thủy') return 'Hỏa';
        if ($menh == 'Mộc') return 'Thổ';
        if ($menh == 'Thổ') return 'Thủy';
        return null;
    }

    public static function menhKhac($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thủy';
        if ($menh == 'Kim') return 'Hỏa';
        if ($menh == 'Thủy') return 'Thổ';
        if ($menh == 'Mộc') return 'Kim';
        if ($menh == 'Thổ') return 'Mộc';
        return null;
    }

    public static function sinhMenh($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Thổ';
        if ($menh == 'Kim') return 'Thủy';
        if ($menh == 'Thủy') return 'Mộc';
        if ($menh == 'Mộc') return 'Hỏa';
        if ($menh == 'Thổ') return 'Kim';
        return null;
    }

    public static function menhSinh($menh)
    {
        $menh = trim($menh);
        if ($menh == 'Hỏa') return 'Mộc';
        if ($menh == 'Kim') return 'Thổ';
        if ($menh == 'Thủy') return 'Kim';
        if ($menh == 'Mộc') return 'Thủy';
        if ($menh == 'Thổ') return 'Hỏa';
        return null;
    }

    protected static $Hythan = [];
    public static function getMenhTinh($can)
    {
        // Gọi hàm lấy mệnh chính
        $Menh = self::getMenh($can);
        self::$Menhchu = $Menh;

        $arrMenh = ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'];
        $arrResult = ['', '', '', '', ''];
        $indexMenhChu = 0;

        // Xác định vị trí mệnh chủ
        foreach ($arrMenh as $index => $item) {
            if ($item == $Menh) {
                $arrResult[$index] = 'Tỷ Kiếp';
                $indexMenhChu = $index;
            }
        }

        // Ấn tinh
        if ($indexMenhChu > 0) {
            $arrResult[$indexMenhChu - 1] = 'Ấn Tinh';
        } else {
            $arrResult[4] = 'Ấn Tinh';
        }

        // Thực thương
        $menh_sinh = self::getMenhThuong($arrMenh[$indexMenhChu]);
        $index_menhsinh = array_search($menh_sinh, $arrMenh);
        if ($index_menhsinh !== false) {
            $arrResult[$index_menhsinh] = 'Thực Thương';
        }

        // Tài tinh
        $menh_khac = self::bikhac($arrMenh[$indexMenhChu]);
        $index_menhchukhac = array_search($menh_khac, $arrMenh);
        if ($index_menhchukhac !== false) {
            $arrResult[$index_menhchukhac] = 'Tài Tinh';
        }

        // Các vị trí còn lại là Quan Sát
        foreach ($arrResult as $i => $item) {
            if ($item == '') {
                $arrResult[$i] = 'Quan Sát';
            }
        }

        self::$Hythan = $arrResult;

        return $arrResult;
    }


    public static function getThapThan(string $nhatCan, string $otherCan, bool $isDay = false): string
    {
        if ($isDay) return 'Nhật Can';

        $iDay   = array_search($nhatCan, self::$stems);
        $iOther = array_search($otherCan, self::$stems);
        if ($iDay === false || $iOther === false) return '';

        $elDay   = self::$elements[$iDay];
        $elOther = self::$elements[$iOther];
        $yyDay   = self::$yinYang[$iDay];
        $yyOther = self::$yinYang[$iOther];

        if ($elDay === $elOther) {
            return $yyDay === $yyOther ? 'Tỷ' : 'Kiếp Tài';
        }

        // Ngũ hành sinh khắc
        $cycle = ['Mộc', 'Hỏa', 'Thổ', 'Kim', 'Thủy'];
        $iElDay   = array_search($elDay, $cycle);
        $iElOther = array_search($elOther, $cycle);

        if ($iElOther === ($iElDay + 1) % 5) {
            // Nhật Can sinh ra Other => Tài
            return $yyDay === $yyOther ? 'Thiên Tài' : 'Chính Tài';
        } elseif ($iElOther === ($iElDay + 2) % 5) {
            // Other khắc Nhật Can => Quan Sát
            return $yyDay === $yyOther ? 'Sát' : 'Quan';
        } elseif ($iElOther === ($iElDay + 3) % 5) {
            // Nhật Can bị khắc => Thực Thương
            return $yyDay === $yyOther ? 'Thương Quan' : 'Thực Thần';
        } elseif ($iElOther === ($iElDay + 4) % 5) {
            // Other sinh Nhật Can => Ấn
            return $yyDay === $yyOther ? 'Thiên Ấn' : 'Chính Ấn';
        }

        return '';
    }



    protected static function getChuTinhWebVN(string $dayStem, string $otherStem): string
    {
        $chuTinhTable = [
            'Giáp' => [
                'Giáp' => 'Tỷ',
                'Ất'   => 'Kiếp',
                'Bính' => 'Thực',
                'Đinh' => 'Thương',
                'Mậu'  => 'Thiên Tài',
                'Kỷ'   => 'Chính Tài',
                'Canh' => 'Sát',
                'Tân'  => 'Quan',
                'Nhâm' => 'Thiên Ấn',
                'Quý'  => 'Chính Ấn',
            ],
            'Ất' => [
                'Giáp' => 'Kiếp',
                'Ất'   => 'Tỷ',
                'Bính' => 'Thương',
                'Đinh' => 'Thực',
                'Mậu'  => 'Chính Tài',
                'Kỷ'   => 'Thiên Tài',
                'Canh' => 'Quan',
                'Tân'  => 'Sát',
                'Nhâm' => 'Chính Ấn',
                'Quý'  => 'Thiên Ấn',
            ],
            'Bính' => [
                'Giáp' => 'Thiên Ấn',
                'Ất'   => 'Chính Ấn',
                'Bính' => 'Tỷ',
                'Đinh' => 'Kiếp',
                'Mậu'  => 'Thực',
                'Kỷ'   => 'Thương',
                'Canh' => 'Thiên Tài',
                'Tân'  => 'Chính Tài',
                'Nhâm' => 'Sát',
                'Quý'  => 'Quan',
            ],
            'Đinh' => [
                'Giáp' => 'Chính Ấn',
                'Ất'   => 'Thiên Ấn',
                'Bính' => 'Kiếp',
                'Đinh' => 'Tỷ',
                'Mậu'  => 'Thương',
                'Kỷ'   => 'Thực',
                'Canh' => 'Chính Tài',
                'Tân'  => 'Thiên Tài',
                'Nhâm' => 'Quan',
                'Quý'  => 'Sát',
            ],
            'Mậu' => [
                'Giáp' => 'Sát',
                'Ất'   => 'Quan',
                'Bính' => 'Thiên Ấn',
                'Đinh' => 'Chính Ấn',
                'Mậu'  => 'Tỷ',
                'Kỷ'   => 'Kiếp',
                'Canh' => 'Thực',
                'Tân'  => 'Thương',
                'Nhâm' => 'Thiên Tài',
                'Quý'  => 'Chính Tài',
            ],
            'Kỷ' => [
                'Giáp' => 'Quan',
                'Ất'   => 'Sát',
                'Bính' => 'Chính Ấn',
                'Đinh' => 'Thiên Ấn',
                'Mậu'  => 'Kiếp',
                'Kỷ'   => 'Tỷ',
                'Canh' => 'Thương',
                'Tân'  => 'Thực',
                'Nhâm' => 'Chính Tài',
                'Quý'  => 'Thiên Tài',
            ],
            'Canh' => [
                'Giáp' => 'Thiên Tài',
                'Ất'   => 'Chính Tài',
                'Bính' => 'Sát',
                'Đinh' => 'Quan',
                'Mậu'  => 'Thiên Ấn',
                'Kỷ'   => 'Chính Ấn',
                'Canh' => 'Tỷ',
                'Tân'  => 'Kiếp',
                'Nhâm' => 'Thực',
                'Quý'  => 'Thương',
            ],
            'Tân' => [
                'Giáp' => 'Chính Tài',
                'Ất'   => 'Thiên Tài',
                'Bính' => 'Quan',
                'Đinh' => 'Sát',
                'Mậu'  => 'Chính Ấn',
                'Kỷ'   => 'Thiên Ấn',
                'Canh' => 'Kiếp',
                'Tân'  => 'Tỷ',
                'Nhâm' => 'Thương',
                'Quý'  => 'Thực',
            ],
            'Nhâm' => [
                'Giáp' => 'Thực',
                'Ất'   => 'Thương',
                'Bính' => 'Thiên Tài',
                'Đinh' => 'Chính Tài',
                'Mậu'  => 'Sát',
                'Kỷ'   => 'Quan',
                'Canh' => 'Thiên Ấn',
                'Tân'  => 'Chính Ấn',
                'Nhâm' => 'Tỷ',
                'Quý'  => 'Kiếp',
            ],
            'Quý' => [
                'Giáp' => 'Thương',
                'Ất'   => 'Thực',
                'Bính' => 'Chính Tài',
                'Đinh' => 'Thiên Tài',
                'Mậu'  => 'Quan',
                'Kỷ'   => 'Sát',
                'Canh' => 'Chính Ấn',
                'Tân'  => 'Thiên Ấn',
                'Nhâm' => 'Kiếp',
                'Quý'  => 'Tỷ',
            ],
        ];

        return $chuTinhTable[$dayStem][$otherStem] ?? '';
    }

    // Ngũ hành của Thiên Can
    protected static $elementMap = [
        'Giáp' => 'Mộc',
        'Ất'   => 'Mộc',
        'Bính' => 'Hỏa',
        'Đinh' => 'Hỏa',
        'Mậu'  => 'Thổ',
        'Kỷ'   => 'Thổ',
        'Canh' => 'Kim',
        'Tân'  => 'Kim',
        'Nhâm' => 'Thủy',
        'Quý'  => 'Thủy',
    ];

    protected static function checkAmDuongSaiTho(string $dayCan, string $hourCan): bool
    {
        $yin = ['Ất', 'Đinh', 'Kỷ', 'Tân', 'Quý'];
        $yang = ['Giáp', 'Bính', 'Mậu', 'Canh', 'Nhâm'];

        $dDay = in_array($dayCan, $yang) ? 1 : 0;
        $dHour = in_array($hourCan, $yang) ? 1 : 0;

        return $dDay !== $dHour;
    }


    public static function getDaiVan($birthDate, $monthStem, $monthBranch, $targetYear = null, $forward, $hoursToNextJieqi = null)
    {
        // Tính số tháng bắt đầu đại vận đầu tiên
        if ($hoursToNextJieqi !== null) {
            // Nếu là dương nam âm nữ (thuận): đếm từ sinh đến hết tháng
            // Nếu là âm nam dương nữ (nghịch): đếm từ sinh ngược về đầu tháng
            $startingMonths = ceil($hoursToNextJieqi / 6);
        } else {
            // Nếu không có thông tin tiết khí, dùng giá trị mặc định
            $startingMonths = 0;
        }

        // Tính tuổi bắt đầu đại vận đầu tiên
        $startingAge = floor($startingMonths / 12);

        // Mảng can chi
        $stems = ["Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý"];
        $branches = ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"];

        $stemIndex = array_search($monthStem, $stems);
        $branchIndex = array_search($monthBranch, $branches);
        if ($stemIndex === false || $branchIndex === false) {
            return null;
        }

        if ($targetYear === null) {
            // Trả về thông tin đại vận đầu tiên
            $birthYear = date('Y', strtotime($birthDate));
            $startDate = date('Y-m-d', strtotime($birthDate . " + $startingMonths months"));

            if ($forward) {
                // Thuận: +1 can chi từ tháng sinh
                $canIndex = ($stemIndex + 1) % 10;
                $chiIndex = ($branchIndex + 1) % 12;
            } else {
                // Nghịch: -1 can chi từ tháng sinh
                $canIndex = ($stemIndex - 1 + 10) % 10;
                $chiIndex = ($branchIndex - 1 + 12) % 12;
            }
            $dayCan = self::$battu['day']['can'];
            $thapThan[] = self::getChuTinhWebVN($dayCan, $stems[$canIndex]);
            $thapThan[] = self::getChuTinhWebVN($dayCan, self::$hiddenStems[$branches[$chiIndex]][0]);
            $CantangDaiVan = [];
            foreach (self::$hiddenStems[$branches[$chiIndex]] as $can) {
                $CantangDaiVan[] = self::getChuTinhWebVN($dayCan, $can);
            }
            return [
                'can' => $stems[$canIndex],
                'chi' => $branches[$chiIndex],
                'thap_than' => $thapThan,
                'can_tang_dai_van' => $CantangDaiVan,
                // 'start_date' => $startDate,
                // 'start_age' => $startingAge,
                // 'end_age' => $startingAge + 9,
                // 'start_year' => $birthYear + $startingAge,
                // 'end_year' => $birthYear + $startingAge + 9
            ];
        } else {
            // Tính đại vận cho một năm cụ thể
            $birthYear = date('Y', strtotime($birthDate));
            $age = $targetYear - $birthYear + 1;
            // dd($age);
            // Xác định đang ở đại vận thứ mấy
            $vanIndex = floor(($age - $startingAge) / 10);

            if ($vanIndex < 0) {
                // Chưa đến tuổi bắt đầu đại vận
                // return null; 
                $vanIndex = 0;
            }

            if ($forward) {
                // Thuận: +1 can chi từ tháng sinh, rồi + thêm số vận đã qua
                $canIndex = ($stemIndex + $vanIndex + 1) % 10;
                $chiIndex = ($branchIndex + $vanIndex + 1) % 12;
            } else {
                // Nghịch: -1 can chi từ tháng sinh, rồi - thêm số vận đã qua
                $canIndex = ($stemIndex - $vanIndex - 1 + 20) % 10;
                $chiIndex = ($branchIndex - $vanIndex - 1 + 24) % 12;
            }

            $vanStartAge = $startingAge + ($vanIndex * 10);
            $vanStartYear = $birthYear + $vanStartAge;
            $dayCan = self::$battu['day']['can'];
            // Tính thập thần cho can của đại vận
            $thapThan[] = self::getChuTinhWebVN($dayCan, $stems[$canIndex]);
            $thapThan[] = self::getChuTinhWebVN($dayCan, self::$hiddenStems[$branches[$chiIndex]][0]); // Can tàng của chi ngày
            $CantangDaiVan = [];
            foreach (self::$hiddenStems[$branches[$chiIndex]] as $can) {
                $CantangDaiVan[] = self::getChuTinhWebVN($dayCan, $can);
            }
            return [
                'can' => $stems[$canIndex],
                'chi' => $branches[$chiIndex],
                'thap_than' => $thapThan,
                'can_tang_dai_van' => $CantangDaiVan,
            ];
        }
    }

    protected $diemtongthaydoi = null;

    public static function dungthan($daivan, $thiencan, $diachi_cantang, $ngaysinh, $Songay)
    {
        self::$DaiVan = $daivan;
        $tongthaydoi = self::tongthaydoi($daivan, $thiencan, $diachi_cantang, $ngaysinh, $Songay);
        return self::tinhdiem($thiencan, $diachi_cantang, $tongthaydoi[0], $tongthaydoi[1]);
    }

    protected static $ratioNguhanh = [];

    public static function tinhdiem($thiencan, $diachi_cantang, $thaydoithiencan, $thaydoidiachi)
    {

        $tinhdiemnguhanh = self::tinhdiemNguHanh($thiencan, $diachi_cantang, $thaydoithiencan, $thaydoidiachi);
        $ratioNguhanh = self::getPhantram($tinhdiemnguhanh);
        self::$ratioNguhanh = $ratioNguhanh;
        self::$DiemNguHanh = $tinhdiemnguhanh;

        return $ratioNguhanh;
    }

    public static function getPhantram($arr)
    {
        $tong = 0;
        foreach ($arr as $item) {
            $tong += $item;
        }

        $result = [];
        if ($tong == 0) {
            foreach ($arr as $item) {
                $result[] = 0;
            }
            return $result;
        }

        foreach ($arr as $item) {
            $phantram = ($item / $tong) * 100;
            $result[] = round($phantram, 2);
        }

        return $result;
    }

    protected static $Diemthiencaniachi = [];
    protected static $DiemNguHanh = [];

    public static function tinhdiemNguHanh($thiencan, $diachi_cantang, $thaydoithiencan, $thaydoidiachi)
    {
        $Moc = 0;
        $Hoa = 0;
        $Tho = 0;
        $Kim = 0;
        $Thuy = 0;

        $diem = self::tinhdiemthiencan($thaydoithiencan);
        $Diemthiencaniachi = [];
        $Diemthiencaniachi[] = $diem;

        // --- Tính điểm can tàng ---
        $diemcantang = self::tongdiemcantang($diachi_cantang, $thaydoidiachi);
        $Diemthiencaniachi[] = $diemcantang;
        self::$Diemthiencaniachi = $Diemthiencaniachi;
        // --- Gộp thiên can gốc + thiên can đại vận ---
        $thiencantong = array_merge($thiencan, $diemcantang[0]);

        // Gộp điểm thiên can + điểm can tàng
        $diem = array_merge($diem, $diemcantang[1]);

        // --- Tính tổng điểm theo Ngũ hành ---
        foreach ($thiencantong as $index => $item) {
            $value = $diem[$index] ?? 0;

            if ($item === 'Giáp' || $item === 'Ất') {
                $Moc += $value;
            }
            if ($item === 'Bính' || $item === 'Đinh') {
                $Hoa += $value;
            }
            if ($item === 'Mậu' || $item === 'Kỷ') {
                $Tho += $value;
            }
            if ($item === 'Canh' || $item === 'Tân') {
                $Kim += $value;
            }
            if ($item === 'Nhâm' || $item === 'Quý') {
                $Thuy += $value;
            }
        }

        self::$DiemNguHanh = [$Moc, $Hoa, $Tho, $Kim, $Thuy];

        // --- Kiểm tra Hoà hợp Ngũ hành ---
        self::checkHoaHop($diachi_cantang, $thiencan, $thiencantong, $diem);

        return self::$DiemNguHanh;
    }

    protected static $DiemThienCan = [];
    protected static $chinhankesat = 0;
    protected static $Menhchu = '';

    protected static $ArrDiachi = [];
    protected static $Arrdiachi_cantang = [];
    protected static $ArrThienCan = [];
    protected static $ArrThienCanTong = [];

    protected static $TamHop = [
        ['Dần', 'Ngọ', 'Tuất'], // Hỏa
        ['Tỵ', 'Dậu', 'Sửu'],   // Kim
        ['Thân', 'Tý', 'Thìn'], // Thủy
        ['Hợi', 'Mão', 'Mùi'],  // Mộc
    ];

    protected static $TamHoi = [
        ['Tỵ', 'Ngọ', 'Mùi'], //Hỏa 
        ['Thân', 'Dậu', 'Tuất'], //Kim 
        ['Hợi', 'Tý', 'Sửu'], //Thủy 
        ['Dần', 'Mão', 'Thìn'], //Mộc 
        ['Thìn', 'Tuất', 'Sửu', 'Mùi'], //Thổ 
    ];

    public static function checkTamHop($menh)
    {
        $nguhanhTamHop = ['Hỏa', 'Kim', 'Thủy', 'Mộc'];
        $indexMenh = array_search($menh, $nguhanhTamHop);

        if ($indexMenh === false) return false; // nếu không thuộc nhóm hợp nào

        $set = [];

        foreach (self::$ArrDiachi as $item) {
            if (in_array($item, self::$TamHop[$indexMenh])) {
                $set[$item] = true; // dùng key để loại trùng, như Set trong JS
            }
        }

        return count($set) === 3;
    }



    public static function checkTamHoi($menh)
    {
        $nguhanhTamHoi = ['Hỏa', 'Kim', 'Thủy', 'Mộc', 'Thổ'];
        $indexMenh = array_search($menh, $nguhanhTamHoi);

        if ($indexMenh === false) return false;

        $set = [];

        foreach (self::$ArrDiachi as $item) {
            if (in_array($item, self::$TamHoi[$indexMenh])) {
                $set[$item] = true; // mô phỏng Set của JS
            }
        }

        return count($set) === 3;
    }

    public static function checkHoiHop($menh)
    {
        if (self::checkTamHop($menh) || self::checkTamHoi($menh)) return true;
        return false;
    }

    public static function tinhlaidiemNguHanh($indexDiem, $indexMenhTru, $indexMenhCong)
    {
        $diem = self::$DiemThienCan[$indexDiem];
        self::$DiemNguHanh[$indexMenhTru] -= $diem;
        self::$DiemNguHanh[$indexMenhCong] += $diem;
    }

    public static function addChuyenHoaTC($menhTru, $menh, $index)
    {
        if ($menhTru == self::$Menhchu && $menh == self::$Menhchu) {
            self::$arrNC[$index] = 1;
            return;
        }

        if ($menhTru == $menh) {
            self::$arrNC[$index] = -1;
            return;
        }

        if (self::$Menhchu == $menh && $menhTru != $menh) {
            self::$arrNC[$index] = 1;
            return;
        }

        self::$arrNC[$index] = -1;
    }

    public static function checkTamHinh($arr)
    {

        // Nếu $arr là chuỗi (ví dụ: 'Mùi,Tuất,Sửu') => tách thành mảng
        if (is_string($arr)) {
            $arr = array_map('trim', explode(',', $arr));
        }

        $set = [];
        foreach (self::$ArrDiachi as $item) {
            if (in_array($item, $arr)) {
                $set[$item] = true; // dùng key để tránh trùng
            }
        }

        return count($set) == 3;
    }

    public static function checkGiapKy()
    {
        // Lấy Thiên Can của Đại Vận (phần đầu)
        $thiencanDV = explode(' ', self::$DaiVan)[0];

        // Nếu thuộc nhóm này thì không xét hóa hợp
        if (in_array($thiencanDV, ['Giáp', 'Ất', 'Kỷ', 'Canh'])) {
            return false;
        }

        // TH1: Mộc mạnh hơn Thổ
        if (self::$DiemNguHanh[2] > self::$DiemNguHanh[0]) {
            if (
                self::checkHoiHop('Hỏa') || self::checkHoiHop('Mộc') ||
                self::checkHoiHop('Kim') || self::checkHoiHop('Thủy')
            ) {
                return false;
            }

            if (
                self::$ArrThienCan[0] == 'Giáp' && self::$ArrThienCan[1] == 'Kỷ' &&
                self::$ArrThienCan[2] != 'Giáp' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(0, 0, 2);
                self::addChuyenHoaTC('Mộc', 'Thổ', 0);
                self::addChuyenHoaTC('Thổ', 'Thổ', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Kỷ' && self::$ArrThienCan[1] == 'Giáp' &&
                self::$ArrThienCan[2] != 'Kỷ' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 0, 2);
                self::addChuyenHoaTC('Mộc', 'Thổ', 1);
                self::addChuyenHoaTC('Thổ', 'Thổ', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Kỷ' && self::$ArrThienCan[1] == 'Giáp' &&
                self::$ArrThienCan[2] == 'Kỷ' && self::$ArrThienCan[3] != 'Giáp' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 0, 2);
                self::addChuyenHoaTC('Mộc', 'Thổ', 1);
                self::addChuyenHoaTC('Thổ', 'Thổ', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Giáp' && self::$ArrThienCan[2] == 'Kỷ' &&
                self::$ArrThienCan[3] == 'Giáp' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(2, 0, 2);
                self::addChuyenHoaTC('Mộc', 'Thổ', 3);
                self::addChuyenHoaTC('Thổ', 'Thổ', 2);
                return true;
            }
        }

        // TH2: Thổ mạnh hơn Mộc
        if (self::$DiemNguHanh[0] > self::$DiemNguHanh[2]) {
            if (
                self::checkHoiHop('Hỏa') || self::checkHoiHop('Kim') ||
                self::checkHoiHop('Thủy') || self::checkTamHinh('Mùi,Tuất,Sửu')
            ) {
                return false;
            }

            if (
                self::$ArrThienCan[0] == 'Giáp' && self::$ArrThienCan[1] == 'Kỷ' &&
                self::$ArrThienCan[2] != 'Giáp' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(1, 2, 0);
                self::addChuyenHoaTC('Thổ', 'Mộc', 1);
                self::addChuyenHoaTC('Mộc', 'Mộc', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Kỷ' && self::$ArrThienCan[1] == 'Giáp' &&
                self::$ArrThienCan[2] != 'Kỷ' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(0, 2, 0);
                self::addChuyenHoaTC('Thổ', 'Mộc', 0);
                self::addChuyenHoaTC('Mộc', 'Mộc', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Giáp' && self::$ArrThienCan[1] == 'Kỷ' &&
                self::$ArrThienCan[2] == 'Giáp' && self::$ArrThienCan[3] != 'Kỷ' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(1, 2, 0);
                self::addChuyenHoaTC('Thổ', 'Mộc', 1);
                self::addChuyenHoaTC('Mộc', 'Mộc', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Kỷ' && self::$ArrThienCan[2] == 'Giáp' &&
                self::$ArrThienCan[3] == 'Kỷ' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(2, 2, 0);
                self::addChuyenHoaTC('Thổ', 'Mộc', 3);
                self::addChuyenHoaTC('Mộc', 'Mộc', 2);
                return true;
            }
        }

        return false;
    }
    public static function checkAtCanh()
    {
        // global $DaiVan, $DiemNguHanh, $ArrThienCan, $ArrDiachi, $DiemThienCan, $Menhchu;

        $thiencanDV = explode(' ', self::$DaiVan)[0];
        if (in_array($thiencanDV, ['Ất', 'Canh', 'Tân', 'Bính'])) {
            return false;
        }

        // --- Khi Kim mạnh hơn Mộc ---
        if (self::$DiemNguHanh[3] > self::$DiemNguHanh[0]) {
            if (
                self::checkHoiHop('Hỏa') ||
                self::checkHoiHop('Mộc') ||
                self::checkHoiHop('Thủy') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Ất' && self::$ArrThienCan[1] == 'Canh' && self::$ArrThienCan[2] != 'Ất' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(0, 0, 3);
                self::addChuyenHoaTC('Mộc', 'Kim', 0);
                self::addChuyenHoaTC('Kim', 'Kim', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Canh' && self::$ArrThienCan[1] == 'Ất' && self::$ArrThienCan[2] != 'Canh' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(1, 0, 3);
                self::addChuyenHoaTC('Mộc', 'Kim', 1);
                self::addChuyenHoaTC('Kim', 'Kim', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Canh' && self::$ArrThienCan[1] == 'Ất' && self::$ArrThienCan[2] != 'Canh' && self::$ArrThienCan[3] != 'Ất' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(1, 0, 3);
                self::addChuyenHoaTC('Mộc', 'Kim', 1);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Ất' && self::$ArrThienCan[2] != 'Canh' && self::$ArrThienCan[3] == 'Ất' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(3, 0, 3);
                self::addChuyenHoaTC('Mộc', 'Kim', 3);
                return true;
            }
        }

        // --- Khi Kim yếu hơn Mộc ---
        if (self::$DiemNguHanh[3] < self::$DiemNguHanh[0]) {
            if (
                self::checkHoiHop('Hỏa') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Thủy') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Ất' && self::$ArrThienCan[1] == 'Canh' && self::$ArrThienCan[2] != 'Ất' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(1, 3, 0);
                self::addChuyenHoaTC('Kim', 'Mộc', 1);
                self::addChuyenHoaTC('Mộc', 'Mộc', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Canh' && self::$ArrThienCan[1] == 'Ất' && self::$ArrThienCan[2] != 'Canh' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(0, 3, 0);
                self::addChuyenHoaTC('Kim', 'Mộc', 0);
                self::addChuyenHoaTC('Mộc', 'Mộc', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Ất' && self::$ArrThienCan[1] == 'Canh' && self::$ArrThienCan[2] != 'Ất' && self::$ArrThienCan[3] != 'Canh' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(1, 3, 0);
                self::addChuyenHoaTC('Kim', 'Mộc', 1);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Canh' && self::$ArrThienCan[2] == 'Ất' && self::$ArrThienCan[3] == 'Canh' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Hợi', 'Tý'])
            ) {
                self::tinhlaidiemNguHanh(3, 3, 0);
                self::addChuyenHoaTC('Kim', 'Mộc', 3);
                self::addChuyenHoaTC('Mộc', 'Mộc', 2);
                return true;
            }
        }

        return false;
    }

    public static function checkBinhTan()
    {
        // global $DaiVan, $DiemNguHanh, $ArrThienCan, $ArrDiachi;

        $thiencanDV = explode(' ', self::$DaiVan)[0];
        if (in_array($thiencanDV, ['Bính', 'Tân', 'Nhâm', 'Đinh'])) {
            return false;
        }

        // --- Trường hợp Thủy mạnh hơn Hỏa và Kim ---
        if (self::$DiemNguHanh[4] > self::$DiemNguHanh[1] && self::$DiemNguHanh[4] > self::$DiemNguHanh[3]) {
            if (
                self::checkHoiHop('Hỏa') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Mộc') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Bính' && self::$ArrThienCan[1] == 'Tân' && self::$ArrThienCan[2] != 'Bính' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(0, 1, 4);
                self::tinhlaidiemNguHanh(1, 3, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 0);
                self::addChuyenHoaTC('Kim', 'Thủy', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Tân' && self::$ArrThienCan[1] == 'Bính' && self::$ArrThienCan[2] != 'Tân' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 4);
                self::tinhlaidiemNguHanh(0, 3, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 1);
                self::addChuyenHoaTC('Kim', 'Thủy', 0);
                return true;
            }
        }

        // --- Trường hợp Kim mạnh hơn Hỏa ---
        if (self::$DiemNguHanh[3] > self::$DiemNguHanh[1]) {
            if (
                self::checkHoiHop('Hỏa') ||
                self::checkHoiHop('Thủy') ||
                self::checkHoiHop('Mộc') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Tân' && self::$ArrThienCan[1] == 'Bính' && self::$ArrThienCan[2] != 'Tân' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 3);
                self::addChuyenHoaTC('Hỏa', 'Kim', 1);
                self::addChuyenHoaTC('Kim', 'Kim', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Bính' && self::$ArrThienCan[1] == 'Tân' && self::$ArrThienCan[2] != 'Bính' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(0, 1, 3);
                self::addChuyenHoaTC('Hỏa', 'Kim', 0);
                self::addChuyenHoaTC('Kim', 'Kim', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Tân' && self::$ArrThienCan[1] == 'Bính' && self::$ArrThienCan[2] == 'Tân' &&
                self::$ArrThienCan[3] != 'Bính' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 3);
                self::addChuyenHoaTC('Hỏa', 'Kim', 1);
                self::addChuyenHoaTC('Kim', 'Kim', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Bính' && self::$ArrThienCan[2] == 'Tân' && self::$ArrThienCan[3] == 'Bính' &&
                in_array(self::$ArrDiachi[1], ['Thân', 'Dậu', 'Thìn', 'Tuất', 'Sửu', 'Mùi'])
            ) {
                self::tinhlaidiemNguHanh(3, 1, 3);
                self::addChuyenHoaTC('Hỏa', 'Kim', 3);
                self::addChuyenHoaTC('Kim', 'Kim', 2);
                return true;
            }
        }

        // --- Trường hợp Hỏa mạnh hơn Kim ---
        if (self::$DiemNguHanh[3] > self::$DiemNguHanh[1]) {
            if (
                self::checkHoiHop('Thủy') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Mộc') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Tân' && self::$ArrThienCan[1] == 'Bính' && self::$ArrThienCan[2] != 'Tân' &&
                in_array(self::$ArrDiachi[1], ['Ngọ', 'Tỵ'])
            ) {
                self::tinhlaidiemNguHanh(0, 3, 1);
                self::addChuyenHoaTC('Kim', 'Hỏa', 0);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Bính' && self::$ArrThienCan[1] == 'Tân' && self::$ArrThienCan[2] != 'Bính' &&
                in_array(self::$ArrDiachi[1], ['Ngọ', 'Tỵ'])
            ) {
                self::tinhlaidiemNguHanh(1, 3, 1);
                self::addChuyenHoaTC('Kim', 'Hỏa', 1);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 0);
                return true;
            }
        }

        return false;
    }

    public static function checkDinhNham()
    {
        // global $DaiVan, $DiemNguHanh, $ArrThienCan, $ArrDiachi;

        $thiencanDV = explode(' ', self::$DaiVan)[0];
        if (in_array($thiencanDV, ['Quý', 'Mậu', 'Nhâm', 'Đinh'])) {
            return false;
        }

        // --- Trường hợp Mộc mạnh hơn Hỏa và Thổ ---
        if (self::$DiemNguHanh[0] > self::$DiemNguHanh[1] && self::$DiemNguHanh[0] > self::$DiemNguHanh[4]) {
            if (
                self::checkHoiHop('Thủy') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Hỏa') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Đinh' && self::$ArrThienCan[1] == 'Nhâm' &&
                self::$ArrThienCan[2] != 'Đinh' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(0, 1, 0);
                self::tinhlaidiemNguHanh(1, 4, 0);
                self::addChuyenHoaTC('Hỏa', 'Mộc', 0);
                self::addChuyenHoaTC('Thủy', 'Mộc', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Nhâm' && self::$ArrThienCan[1] == 'Đinh' &&
                self::$ArrThienCan[2] != 'Nhâm' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 0);
                self::tinhlaidiemNguHanh(0, 4, 0);
                self::addChuyenHoaTC('Hỏa', 'Mộc', 1);
                self::addChuyenHoaTC('Thủy', 'Mộc', 0);
                return true;
            }
        }

        // --- Trường hợp Thủy mạnh hơn Hỏa ---
        if (self::$DiemNguHanh[4] > self::$DiemNguHanh[1]) {
            if (
                self::checkHoiHop('Mộc') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Hỏa') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Đinh' && self::$ArrThienCan[1] == 'Nhâm' &&
                self::$ArrThienCan[2] != 'Đinh' &&
                in_array(self::$ArrDiachi[1], ['Dậu', 'Thân', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(0, 1, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 0);
                self::addChuyenHoaTC('Thủy', 'Thủy', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Nhâm' && self::$ArrThienCan[1] == 'Đinh' &&
                self::$ArrThienCan[2] != 'Nhâm' &&
                in_array(self::$ArrDiachi[1], ['Dậu', 'Thân', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 1);
                self::addChuyenHoaTC('Thủy', 'Thủy', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Nhâm' && self::$ArrThienCan[1] == 'Đinh' &&
                self::$ArrThienCan[2] == 'Nhâm' && self::$ArrThienCan[3] != 'Đinh' &&
                in_array(self::$ArrDiachi[1], ['Dậu', 'Thân', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(1, 1, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 1);
                self::addChuyenHoaTC('Thủy', 'Thủy', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Đinh' && self::$ArrThienCan[2] == 'Nhâm' &&
                self::$ArrThienCan[3] == 'Đinh' &&
                in_array(self::$ArrDiachi[1], ['Dậu', 'Thân', 'Tý', 'Hợi'])
            ) {
                self::tinhlaidiemNguHanh(3, 1, 4);
                self::addChuyenHoaTC('Hỏa', 'Thủy', 3);
                self::addChuyenHoaTC('Thủy', 'Thủy', 2);
                return true;
            }
        }

        // --- Trường hợp Hỏa mạnh hơn Thủy ---
        if (self::$DiemNguHanh[4] > self::$DiemNguHanh[1]) {
            if (
                self::checkHoiHop('Mộc') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Thủy') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Nhâm' && self::$ArrThienCan[1] == 'Đinh' &&
                self::$ArrThienCan[2] != 'Nhâm' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(0, 4, 1);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 0);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Đinh' && self::$ArrThienCan[1] == 'Nhâm' &&
                self::$ArrThienCan[2] != 'Đinh' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 4, 1);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 1);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Đinh' && self::$ArrThienCan[1] == 'Nhâm' &&
                self::$ArrThienCan[2] == 'Đinh' && self::$ArrThienCan[3] != 'Nhâm' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 4, 1);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 1);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Nhâm' && self::$ArrThienCan[2] == 'Đinh' &&
                self::$ArrThienCan[3] == 'Nhâm' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(3, 4, 1);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 3);
                self::addChuyenHoaTC('Hỏa', 'Hỏa', 2);
                return true;
            }
        }

        return false;
    }

    public static function checkCanMau()
    {
        // global $DaiVan, $DiemNguHanh, $ArrThienCan, $ArrDiachi;

        $thiencanDV = explode(' ', self::$DaiVan)[0];
        if (in_array($thiencanDV, ['Quý', 'Mậu', 'Giáp', 'Kỷ'])) {
            return false;
        }

        // --- Trường hợp Thổ mạnh hơn Thủy và Mộc ---
        if (self::$DiemNguHanh[1] > self::$DiemNguHanh[4] && self::$DiemNguHanh[1] > self::$DiemNguHanh[2]) {
            if (
                self::checkHoiHop('Thủy') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Mộc') ||
                self::checkTamHinh('Mùi,Tuất,Sửu')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Mậu' && self::$ArrThienCan[1] == 'Quý' &&
                self::$ArrThienCan[2] != 'Mậu' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(0, 4, 1);
                self::tinhlaidiemNguHanh(1, 2, 1);
                self::addChuyenHoaTC('Thổ', 'Hỏa', 0);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Quý' && self::$ArrThienCan[1] == 'Mậu' &&
                self::$ArrThienCan[2] != 'Quý' &&
                in_array(self::$ArrDiachi[1], ['Dần', 'Mão', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(0, 4, 1);
                self::tinhlaidiemNguHanh(1, 2, 1);
                self::addChuyenHoaTC('Thổ', 'Hỏa', 1);
                self::addChuyenHoaTC('Thủy', 'Hỏa', 0);
                return true;
            }
        }

        // --- Trường hợp Thủy mạnh hơn Thổ ---
        if (self::$DiemNguHanh[2] > self::$DiemNguHanh[4]) {
            if (
                self::checkHoiHop('Thủy') ||
                self::checkHoiHop('Kim') ||
                self::checkHoiHop('Mộc') ||
                self::checkHoiHop('Hỏa')
            ) return false;

            if (
                self::$ArrThienCan[0] == 'Quý' && self::$ArrThienCan[1] == 'Mậu' &&
                self::$ArrThienCan[2] != 'Quý' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(0, 4, 2);
                self::addChuyenHoaTC('Thủy', 'Thổ', 0);
                self::addChuyenHoaTC('Thổ', 'Thổ', 1);
                return true;
            }

            if (
                self::$ArrThienCan[0] == 'Mậu' && self::$ArrThienCan[1] == 'Quý' &&
                self::$ArrThienCan[2] != 'Mậu' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 4, 2);
                self::addChuyenHoaTC('Thủy', 'Thổ', 1);
                self::addChuyenHoaTC('Thổ', 'Thổ', 0);
                return true;
            }

            if (
                self::$ArrThienCan[0] != 'Mậu' && self::$ArrThienCan[1] == 'Quý' &&
                self::$ArrThienCan[2] == 'Mậu' && self::$ArrThienCan[3] != 'Quý' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(1, 4, 2);
                self::addChuyenHoaTC('Thủy', 'Thổ', 1);
                self::addChuyenHoaTC('Thổ', 'Thổ', 2);
                return true;
            }

            if (
                self::$ArrThienCan[1] != 'Quý' && self::$ArrThienCan[2] == 'Mậu' &&
                self::$ArrThienCan[3] == 'Quý' &&
                in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Tỵ', 'Ngọ'])
            ) {
                self::tinhlaidiemNguHanh(3, 4, 2);
                self::addChuyenHoaTC('Thủy', 'Thổ', 3);
                self::addChuyenHoaTC('Thổ', 'Thổ', 2);
                return true;
            }
        }

        return false;
    }

    public static function getMaxIndex($arr)
    {
        if (empty($arr)) return -1;
        $maxPoint = $arr[0];
        $indexMax = 0;
        foreach ($arr as $index => $value) {
            if ($value > $maxPoint) {
                $maxPoint = $value;
                $indexMax = $index;
            }
        }
        return $indexMax;
    }

    public static function canhnhau($str1, $str2)
    {

        $index1 = array_search($str1, self::$ArrDiachi);
        $index2 = -1;

        if ($index1 != -1) {
            foreach (self::$ArrDiachi as $index => $item) {
                if ($item == $str1) {
                    if ($index - 1 >= 0 && self::$ArrDiachi[$index - 1] == $str2) {
                        $index2 = $index - 1;
                    }
                    if ($index + 1 <= 4 && isset(self::$ArrDiachi[$index + 1]) && self::$ArrDiachi[$index + 1] == $str2) {
                        $index2 = $index + 1;
                    }
                }
            }
        }

        if ($index2 != -1) {
            return [$index1, $index2];
        }

        return false;
    }

    public static function chuyenhoadiachi($indexDiem, $item, $indexCong)
    {
        if ($item == 'Giáp' || $item == 'Ất') {
            self::tinhlaidiemNguHanh($indexDiem, 0, $indexCong);
        }

        if ($item == 'Bính' || $item == 'Đinh') {
            self::tinhlaidiemNguHanh($indexDiem, 1, $indexCong);
        }

        if ($item == 'Mậu' || $item == 'Kỷ') {
            self::tinhlaidiemNguHanh($indexDiem, 2, $indexCong);
        }

        if ($item == 'Canh' || $item == 'Tân') {
            self::tinhlaidiemNguHanh($indexDiem, 3, $indexCong);
        }

        if ($item == 'Nhâm' || $item == 'Quý') {
            self::tinhlaidiemNguHanh($indexDiem, 4, $indexCong);
        }
    }


    public static function checkTySuuTho()
    {
        // global $DaiVan, $DiemNguHanh, $ArrThienCan, $ArrDiachi, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        // Loại trừ các địa chi bất lợi
        if (in_array($diachiDV, ['Tý', 'Sửu', 'Ngọ', 'Mùi'])) return false;

        // Kiểm tra có Mậu/Kỷ trong thiên can chính
        if (
            !(
                in_array(self::$ArrThienCan[0], ['Mậu', 'Kỷ']) ||
                in_array(self::$ArrThienCan[1], ['Mậu', 'Kỷ']) ||
                in_array(self::$ArrThienCan[3], ['Mậu', 'Kỷ'])
            )
        ) return false;

        // Nếu chi thứ 2 là Tý/Sửu mà xung chi sai thì loại
        if (self::$ArrDiachi[2] == 'Tý' && in_array(self::$ArrDiachi[2], ['Ngọ', 'Thìn'])) return false;
        if (self::$ArrDiachi[2] == 'Sửu' && in_array(self::$ArrDiachi[2], ['Mùi', 'Dậu'])) return false;

        // Kiểm tra ngũ hành mạnh
        $indexMax = self::getMaxIndex(self::$DiemNguHanh);
        if (
            !(self::$DiemNguHanh[2] > self::$DiemNguHanh[4]) &&
            !($indexMax == 1 || $indexMax == 2)
        ) return false;

        // Loại trừ các hội hợp khác
        if (
            self::checkHoiHop('Thủy') ||
            self::checkHoiHop('Kim') ||
            self::checkHoiHop('Mộc') ||
            self::checkHoiHop('Hỏa')
        ) return false;

        // Kiểm tra Tý–Sửu có nằm cạnh nhau không
        $canhnhau = self::canhnhau('Tý', 'Sửu');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Tý' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Sửu' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thổ', 'Thủy', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thổ', 'Thổ', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Mậu' && $cantang != 'Kỷ') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 2);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }

    public static function checkNgoMuiTho()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang; // dùng các biến toàn cục như JS

        $diachiDV = explode(' ', self::$DaiVan)[1];

        if (in_array($diachiDV, ['Tý', 'Sửu', 'Ngọ', 'Mùi']))
            return false;

        if (!(in_array('Mậu', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) || in_array('Kỷ', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])))
            return false;

        // if (!in_array($ArrDiachi[1], ['Thìn','Tuất','Tỵ','Sửu','Ngọ','Mùi']))
        //     return false;

        if (self::$ArrDiachi[2] == 'Ngọ' && in_array(self::$ArrDiachi[2], ['Tý', 'Dần']))
            return false;

        if (self::$ArrDiachi[2] == 'Mùi' && in_array(self::$ArrDiachi[2], ['Sửu', 'Mão']))
            return false;

        $indexMax = self::getMaxIndex(self::$DiemNguHanh);

        if (!($indexMax == 2 || $indexMax == 1))
            return false;

        if (self::checkHoiHop('Thủy') || self::checkHoiHop('Kim') || self::checkHoiHop('Mộc') || self::checkHoiHop('Hỏa'))
            return false;

        $canhnhau = self::canhnhau('Ngọ', 'Mùi');
        // echo "canhnhau ngo mui: "; print_r($canhnhau);

        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (($item['diachi'] == 'Ngọ' && $index == $canhnhau[0]) || ($item['diachi'] == 'Mùi' && $index == $canhnhau[1])) {

                    self::addChuyenHoaTC('Thổ', 'Hỏa', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thổ', 'Thổ', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Mậu' && $cantang != 'Kỷ') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 2);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }

    public static function checkDanHoiMoc()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $diachiDV = explode(' ', self::$DaiVan)[1];

        if (in_array($diachiDV, ['Dần', 'Hợi', 'Thân', 'Tỵ']))
            return false;

        if (!(in_array('Giáp', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) || in_array('Ất', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])))
            return false;

        if (self::$ArrDiachi[2] == 'Dần' && in_array(self::$ArrDiachi[2], ['Thân', 'Ngọ']))
            return false;

        if (self::$ArrDiachi[2] == 'Hợi' && in_array(self::$ArrDiachi[2], ['Tỵ', 'Mão']))
            return false;

        $indexMax = self::getMaxIndex(self::$DiemNguHanh);

        if (!($indexMax == 0 || $indexMax == 4))
            return false;

        if (self::checkHoiHop('Thủy') || self::checkHoiHop('Kim') || self::checkHoiHop('Hỏa') || self::checkTamHinh('Mùi,Tuất,Sửu'))
            return false;

        $canhnhau = self::canhnhau('Dần', 'Hợi');

        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (($item['diachi'] == 'Dần' && $index == $canhnhau[0]) || ($item['diachi'] == 'Hợi' && $index == $canhnhau[1])) {

                    self::addChuyenHoaTC('Thủy', 'Mộc', $canhnhau[1] + 4);
                    self::addChuyenHoaTC('Mộc', 'Mộc', $canhnhau[0] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Giáp' && $cantang != 'Ất') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 0);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }

    public static function checkMaoTuatHoa()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $diachiDV = explode(' ', self::$DaiVan)[1];

        if (in_array($diachiDV, ['Mão', 'Tuất', 'Dậu', 'Thìn']))
            return false;

        if (!(in_array('Bính', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) || in_array('Đinh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])))
            return false;

        if (self::$ArrDiachi[2] == 'Mão' && in_array(self::$ArrDiachi[2], ['Dậu', 'Hợi']))
            return false;

        if (self::$ArrDiachi[2] == 'Tuất' && in_array(self::$ArrDiachi[2], ['Thìn', 'Ngọ']))
            return false;

        $indexMax = self::getMaxIndex(self::$DiemNguHanh);

        if (!($indexMax == 0 || $indexMax == 1))
            return false;

        if (!(self::$DiemNguHanh[1] > self::$DiemNguHanh[2]))
            return false;

        if (self::checkHoiHop('Thủy') || self::checkHoiHop('Kim') || self::checkHoiHop('Mộc') || self::checkTamHinh('Mùi,Tuất,Sửu'))
            return false;

        $canhnhau = self::canhnhau('Mão', 'Tuất');

        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (($item['diachi'] == 'Mão' && $index == $canhnhau[0]) || ($item['diachi'] == 'Tuất' && $index == $canhnhau[1])) {

                    self::addChuyenHoaTC('Mộc', 'Hỏa', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thổ', 'Hỏa', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Bính' && $cantang != 'Đinh') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 1);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }

    public static function checkThinDauKim()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $diachiDV = explode(' ', self::$DaiVan)[1];

        if (in_array($diachiDV, ['Mão', 'Tuất', 'Dậu', 'Thìn']))
            return false;

        if (!(in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) || in_array('Tân', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])))
            return false;

        if (self::$ArrDiachi[2] == 'Thìn' && in_array(self::$ArrDiachi[2], ['Tuất', 'Tý']))
            return false;

        if (self::$ArrDiachi[2] == 'Dậu' && in_array(self::$ArrDiachi[2], ['Mão', 'Tỵ']))
            return false;

        $indexMax = self::getMaxIndex(self::$DiemNguHanh);

        if (!($indexMax == 3 || $indexMax == 2))
            return false;

        if (self::checkHoiHop('Thủy') || self::checkHoiHop('Hỏa') || self::checkHoiHop('Mộc') || self::checkTamHinh('Mùi,Tuất,Sửu'))
            return false;

        $canhnhau = self::canhnhau('Thìn', 'Dậu');

        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (($item['diachi'] == 'Thìn' && $index == $canhnhau[0]) || ($item['diachi'] == 'Dậu' && $index == $canhnhau[1])) {

                    self::addChuyenHoaTC('Thổ', 'Kim', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Kim', 'Kim', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Canh' && $cantang != 'Tân') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 3);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }

    public static function checkTyThanThuy()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $diachiDV = explode(' ', self::$DaiVan)[1];

        if (in_array($diachiDV, ['Tỵ', 'Thân', 'Hợi', 'Dần']))
            return false;

        if (!(
            in_array('Nhâm', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Quý', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Tân', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (self::$ArrDiachi[2] == 'Tỵ' && in_array(self::$ArrDiachi[2], ['Tuất', 'Tý']))
            return false;

        if (self::$ArrDiachi[2] == 'Thân' && in_array(self::$ArrDiachi[2], ['Mão', 'Tỵ']))
            return false;

        $indexMax = self::getMaxIndex(self::$DiemNguHanh);

        if (!(self::$DiemNguHanh[4] > self::$DiemNguHanh[1]))
            return false;

        if (!($indexMax == 3 || $indexMax == 4))
            return false;

        if (self::checkHoiHop('Kim') || self::checkHoiHop('Hỏa') || self::checkHoiHop('Mộc') || self::checkTamHinh('Mùi,Tuất,Sửu'))
            return false;

        $canhnhau = self::canhnhau('Tỵ', 'Thân');

        if ($canhnhau) {
            $indexCangtang = 4;

            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Tỵ' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Thân' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thủy', 'Thủy', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Kim', 'Thủy', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Nhâm' && $cantang != 'Quý') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 4);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }

        return false;
    }
    public static function checkExits($arr)
    {
        $set = [];

        foreach (self::$ArrDiachi as $item) {
            if (in_array($item, $arr)) {
                $set[$item] = true;
            }
        }

        return count($set) == 3 ? true : false;
    }

    public static function getMenhDiaChi($can)
    {
        if ($can == 'Tý' || $can == 'Hợi')
            return 'Thủy';
        if ($can == 'Sửu' || $can == 'Thìn' || $can == 'Mùi' || $can == 'Tuất')
            return 'Thổ';
        if ($can == 'Dần' || $can == 'Mão')
            return 'Mộc';
        if ($can == 'Tỵ' || $can == 'Ngọ')
            return 'Hỏa';
        if ($can == 'Thân' || $can == 'Dậu')
            return 'Kim';
    }

    public static function getMenhThienCan($can)
    {
        if ($can == 'Giáp' || $can == 'Ất')
            return 'Mộc';
        if ($can == 'Bính' || $can == 'Đinh')
            return 'Hỏa';
        if ($can == 'Mậu' || $can == 'Kỷ')
            return 'Thổ';
        if ($can == 'Canh' || $can == 'Tân')
            return 'Kim';
        if ($can == 'Nhâm' || $can == 'Quý')
            return 'Thủy';
    }


    public static function tinhDiemTamHopHoa($arr, $arrCan, $indexCong)
    {
        // global $ArrDiachi, $Arrdiachi_cantang;

        $count = 0;
        foreach (self::$ArrDiachi as $item) {
            if (in_array($item, $arr)) {
                $count++;
            }
        }

        if ($count == 4) {
            $thiencan = '';
            $arr_index = [];

            foreach (self::$ArrDiachi as $item) {
                $countSame = 0;
                foreach (self::$ArrDiachi as $_item) {
                    if ($item == $_item) {
                        $countSame++;
                    }
                }
                if ($countSame == 2) {
                    $thiencan = $item;
                }
            }

            foreach (self::$ArrDiachi as $index => $item) {
                if ($item == $thiencan) {
                    $arr_index[] = $index;
                }
            }

            // TH1: _arr.includes(4)
            if (in_array(4, $arr_index)) {
                $indexCangtang = 4;
                foreach (self::$Arrdiachi_cantang as $index => $item) {
                    if (in_array($item['diachi'], $arr) && $index != 4) {
                        $menhchuyen = self::getMenhDiaChi($item['diachi']);
                        $menhNhan = self::getMenhThienCan($arrCan[0]);
                        self::addChuyenHoaTC($menhchuyen, $menhNhan, $index + 4);

                        foreach ($item['cantang'] as $_index => $cantang) {
                            if (!in_array($cantang, $arrCan)) {
                                $indexDiem = $indexCangtang + $_index;
                                self::chuyenhoadiachi($indexDiem, $cantang, $indexCong);
                            }
                        }
                    }
                    $indexCangtang += count($item['cantang']);
                }
            }

            // TH2: _arr.includes(2) && _arr.includes(0)
            if (in_array(2, $arr_index) && in_array(0, $arr_index)) {
                $indexCangtang = 4;
                foreach (self::$Arrdiachi_cantang as $index => $item) {
                    if (in_array($item['diachi'], $arr) && $index != 0) {
                        $menhchuyen = self::getMenhDiaChi($item['diachi']);
                        $menhNhan = self::getMenhThienCan($arrCan[0]);
                        self::addChuyenHoaTC($menhchuyen, $menhNhan, $index + 4);

                        foreach ($item['cantang'] as $_index => $cantang) {
                            if (!in_array($cantang, $arrCan)) {
                                $indexDiem = $indexCangtang + $_index;
                                self::chuyenhoadiachi($indexDiem, $cantang, $indexCong);
                            }
                        }
                    }
                    $indexCangtang += count($item['cantang']);
                }
            }

            // TH3: _arr.includes(2) && _arr.includes(1)
            if (in_array(2, $arr_index) && in_array(1, $arr_index)) {
                $indexCangtang = 4;
                foreach (self::$Arrdiachi_cantang as $index => $item) {
                    if (in_array($item['diachi'], $arr) && $index != 2) {
                        $menhchuyen = self::getMenhDiaChi($item['diachi']);
                        $menhNhan = self::getMenhThienCan($arrCan[0]);
                        self::addChuyenHoaTC($menhchuyen, $menhNhan, $index + 4);

                        foreach ($item['cantang'] as $_index => $cantang) {
                            if (!in_array($cantang, $arrCan)) {
                                $indexDiem = $indexCangtang + $_index;
                                self::chuyenhoadiachi($indexDiem, $cantang, $indexCong);
                            }
                        }
                    }
                    $indexCangtang += count($item['cantang']);
                }
            }

            // TH4: _arr.includes(0) && _arr.includes(1)
            if (in_array(0, $arr_index) && in_array(1, $arr_index)) {
                $indexCangtang = 4;
                foreach (self::$Arrdiachi_cantang as $index => $item) {
                    if (in_array($item['diachi'], $arr) && $index != 0) {
                        $menhchuyen = self::getMenhDiaChi($item['diachi']);
                        $menhNhan = self::getMenhThienCan($arrCan[0]);
                        self::addChuyenHoaTC($menhchuyen, $menhNhan, $index + 4);

                        foreach ($item['cantang'] as $_index => $cantang) {
                            if (!in_array($cantang, $arrCan)) {
                                $indexDiem = $indexCangtang + $_index;
                                self::chuyenhoadiachi($indexDiem, $cantang, $indexCong);
                            }
                        }
                    }
                    $indexCangtang += count($item['cantang']);
                }
            }
        } else {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (in_array($item['diachi'], $arr)) {
                    $menhchuyen = self::getMenhDiaChi($item['diachi']);
                    $menhNhan = self::getMenhThienCan($arrCan[0]);
                    self::addChuyenHoaTC($menhchuyen, $menhNhan, $index + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if (!in_array($cantang, $arrCan)) {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, $indexCong);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
        }
    }


    public static function checkDanNgoTuatHoa()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        // Nếu không tồn tại đủ 3 địa chi Dần – Ngọ – Tuất thì dừng
        if (!self::checkExits(['Dần', 'Ngọ', 'Tuất']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        // Nếu địa chi đại vận thuộc các nhóm này thì loại trừ
        if (in_array($diachiDV, ['Tý', 'Mùi', 'Hợi', 'Mão']))
            return false;

        // Kiểm tra thiên can có Bính hoặc Đinh
        if (!(
            in_array('Bính', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Đinh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        // Địa chi trụ tháng (ArrDiachi[1]) phải thuộc nhóm Mão, Tỵ, Dần, Ngọ
        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tỵ', 'Dần', 'Ngọ']))
            return false;

        // Kiểm tra điểm ngũ hành — Hỏa phải lớn hơn Thổ và Mộc
        if (!(self::$DiemNguHanh[1] > self::$DiemNguHanh[2] && self::$DiemNguHanh[1] > self::$DiemNguHanh[0]))
            return false;

        // Gọi hàm tính điểm Tam Hợp Hỏa
        self::tinhDiemTamHopHoa(['Dần', 'Ngọ', 'Tuất'], ['Bính', 'Đinh'], 1);

        return true;
    }

    public static function checkDanNgoHoa()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        // if (!checkExits(['Dần','Ngọ','Tuất']))
        //     return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Tý', 'Mùi', 'Hợi']))
            return false;

        if (
            !(
                in_array('Bính', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Đinh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tỵ', 'Dần', 'Ngọ']))
            return false;

        if (!(self::$DiemNguHanh[1] > self::$DiemNguHanh[2] && self::$DiemNguHanh[1] > self::$DiemNguHanh[0]))
            return false;

        $canhnhau = self::canhnhau('Dần', 'Ngọ'); // hàm này bạn phải định nghĩa riêng

        if ($canhnhau) {
            $indexCangtang = 4;

            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Dần' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Ngọ' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Mộc', 'Hỏa', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Hỏa', 'Hỏa', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Bính' && $cantang != 'Đinh') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 1);
                        }
                    }
                }

                $indexCangtang += count($item['cantang']);
            }

            return true;
        }

        return false;
    }

    public static function checkNgoTuatHoa()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Tý', 'Mùi', 'Mão']))
            return false;

        if (
            !(
                in_array('Bính', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Đinh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tỵ', 'Dần', 'Ngọ']))
            return false;

        if (!(self::$DiemNguHanh[1] > self::$DiemNguHanh[2] && self::$DiemNguHanh[1] > self::$DiemNguHanh[0]))
            return false;

        $canhnhau = self::canhnhau('Ngọ', 'Tuất');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Ngọ' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Tuất' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Hỏa', 'Hỏa', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thổ', 'Hỏa', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Bính' && $cantang != 'Đinh') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 1);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkHoiMaoMuiMoc()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Hợi', 'Mão', 'Mùi']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Dậu', 'Tuất', 'Dần', 'Ngọ']))
            return false;

        if (
            !(
                in_array('Giáp', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Ất', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tý', 'Dần', 'Hợi']))
            return false;

        if (!(self::$DiemNguHanh[0] > self::$DiemNguHanh[2] && self::$DiemNguHanh[0] > self::$DiemNguHanh[4]))
            return false;

        self::tinhDiemTamHopHoa(['Hợi', 'Mão', 'Mùi'], ['Giáp', 'Ất'], 0);
        return true;
    }


    public static function checkHoiMaoMoc()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Dậu', 'Tuất', 'Dần']))
            return false;

        if (
            !(
                in_array('Giáp', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Ất', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tý', 'Dần', 'Hợi']))
            return false;

        if (!(self::$DiemNguHanh[0] > self::$DiemNguHanh[2] && self::$DiemNguHanh[0] > self::$DiemNguHanh[4]))
            return false;

        $canhnhau = self::canhnhau('Hợi', 'Mão');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Hợi' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Mão' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thủy', 'Mộc', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Mộc', 'Mộc', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Giáp' && $cantang != 'Ất') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 0);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkMaoMuiMoc()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Dậu', 'Tuất', 'Ngọ']))
            return false;

        if (
            !(
                in_array('Giáp', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Ất', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Mão', 'Tý', 'Dần', 'Hợi']))
            return false;

        if (!(self::$DiemNguHanh[0] > self::$DiemNguHanh[2] && self::$DiemNguHanh[0] > self::$DiemNguHanh[4]))
            return false;

        $canhnhau = self::canhnhau('Mùi', 'Mão');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Mùi' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Mão' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thổ', 'Mộc', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Mộc', 'Mộc', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Giáp' && $cantang != 'Ất') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 0);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkTyDauSuuKim()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Tý', 'Dậu', 'Sửu']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Mão', 'Thìn', 'Thân', 'Tý']))
            return false;

        if (
            !(
                in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Tân', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[3] > self::$DiemNguHanh[1] && self::$DiemNguHanh[3] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Tý', 'Dậu', 'Sửu'], ['Canh', 'Tân'], 3);
        return true;
    }


    public static function checkTyDauKim()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Mão', 'Thìn', 'Thân']))
            return false;

        if (
            !(
                in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Tân', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[3] > self::$DiemNguHanh[1] && self::$DiemNguHanh[3] > self::$DiemNguHanh[2]))
            return false;

        $canhnhau = self::canhnhau('Tý', 'Dậu');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Tý' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Dậu' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thủy', 'Kim', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Kim', 'Kim', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Canh' && $cantang != 'Tân') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 3);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkDauSuuKim()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = isset($parts[1]) ? $parts[1] : '';

        if (in_array($diachiDV, ['Mão', 'Thìn', 'Tý']))
            return false;

        if (
            !(
                in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
                in_array('Tân', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
            )
        )
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[3] > self::$DiemNguHanh[1] && self::$DiemNguHanh[3] > self::$DiemNguHanh[2]))
            return false;

        $canhnhau = self::canhnhau('Sửu', 'Dậu');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Sửu' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Dậu' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thổ', 'Kim', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Kim', 'Kim', $canhnhau[1] + 4);

                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Canh' && $cantang != 'Tân') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 3);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }

    public static function checkThanTyThinThuy()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Thân', 'Tý', 'Thìn']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Ngọ', 'Sửu', 'Tỵ', 'Dậu']))
            return false;

        if (!(
            in_array('Nhâm', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Quý',  [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Hợi', 'Tý', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[4] > self::$DiemNguHanh[3] && self::$DiemNguHanh[4] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Thân', 'Tý', 'Thìn'], ['Nhâm', 'Quý'], 4);
        return true;
    }


    public static function checkThanTyThuy()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh, $Arrdiachi_cantang;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Ngọ', 'Sửu', 'Tỵ']))
            return false;

        if (!(
            in_array('Nhâm', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Quý',  [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Hợi', 'Tý', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[4] > self::$DiemNguHanh[3] && self::$DiemNguHanh[4] > self::$DiemNguHanh[2]))
            return false;

        $canhnhau = self::canhnhau('Thân', 'Tý');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Thân' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Tý' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Kim', 'Thủy', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thủy', 'Thủy', $canhnhau[1] + 4);
                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Nhâm' && $cantang != 'Quý') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 4);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkTyThinThuy()
    {
        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Ngọ', 'Sửu', 'Dậu']))
            return false;

        if (!(
            in_array('Nhâm', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Quý',  [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Hợi', 'Tý', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[4] > self::$DiemNguHanh[3] && self::$DiemNguHanh[4] > self::$DiemNguHanh[2]))
            return false;

        $canhnhau = self::canhnhau('Thìn', 'Tý');
        if ($canhnhau) {
            $indexCangtang = 4;
            foreach (self::$Arrdiachi_cantang as $index => $item) {
                if (
                    ($item['diachi'] == 'Thìn' && $index == $canhnhau[0]) ||
                    ($item['diachi'] == 'Tý' && $index == $canhnhau[1])
                ) {
                    self::addChuyenHoaTC('Thổ', 'Thủy', $canhnhau[0] + 4);
                    self::addChuyenHoaTC('Thủy', 'Thủy', $canhnhau[1] + 4);
                    foreach ($item['cantang'] as $_index => $cantang) {
                        if ($cantang != 'Nhâm' && $cantang != 'Quý') {
                            $indexDiem = $indexCangtang + $_index;
                            self::chuyenhoadiachi($indexDiem, $cantang, 4);
                        }
                    }
                }
                $indexCangtang += count($item['cantang']);
            }
            return true;
        }
        return false;
    }


    public static function checkHoiTySuuThuy()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Hợi', 'Tý', 'Sửu']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Ngọ', 'Sửu', 'Dần', 'Tý', 'Tỵ', 'Mùi']))
            return false;

        if (!(
            in_array('Nhâm', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Quý',  [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Hợi', 'Tý', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[4] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Hợi', 'Tý', 'Sửu'], ['Nhâm', 'Quý'], 4);
        return true;
    }


    public static function checkDanMaoThinMoc()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Dần', 'Mão', 'Thìn']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Dậu', 'Tuất', 'Hợi', 'Thân']))
            return false;

        if (!(
            in_array('Giáp', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Ất',   [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Hợi', 'Tỵ', 'Dần', 'Mão']))
            return false;

        if (!(self::$DiemNguHanh[0] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Dần', 'Mão', 'Thìn'], ['Giáp', 'Ất'], 0);
        return true;
    }


    public static function checkTyNgoMuiHoa()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Tỵ', 'Ngọ', 'Mùi']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Tý', 'Mùi', 'Sửu', 'Ngọ']))
            return false;

        if (!(
            in_array('Bính', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Đinh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Ngọ', 'Tỵ', 'Dần', 'Mão']))
            return false;

        if (!(self::$DiemNguHanh[1] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Tỵ', 'Ngọ', 'Mùi'], ['Bính', 'Đinh'], 1);
        return true;
    }


    public static function checkThanDauTuatKim()
    {
        // global $DaiVan, $ArrThienCan, $ArrDiachi, $DiemNguHanh;

        if (!self::checkExits(['Thân', 'Dậu', 'Tuất']))
            return false;

        $parts = explode(' ', self::$DaiVan);
        $diachiDV = $parts[1] ?? '';

        if (in_array($diachiDV, ['Mão', 'Thìn', 'Tỵ', 'Dần']))
            return false;

        if (!(
            in_array('Canh', [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]]) ||
            in_array('Tân',  [self::$ArrThienCan[0], self::$ArrThienCan[1], self::$ArrThienCan[3]])
        ))
            return false;

        if (!in_array(self::$ArrDiachi[1], ['Thìn', 'Tuất', 'Sửu', 'Mùi', 'Thân', 'Dậu']))
            return false;

        if (!(self::$DiemNguHanh[3] > self::$DiemNguHanh[2]))
            return false;

        self::tinhDiemTamHopHoa(['Tỵ', 'Ngọ', 'Mùi'], ['Canh', 'Tân'], 3);
        return true;
    }

    public static function checkHoaHop($diachi_cantang, $thiencan, $thiencantong, $diem)
    {

        // Reset arrNC
        self::$arrNC = [0, 0, 0, 0, 0, 0, 0, 0];

        self::$ArrDiachi = array_map(function ($item) {
            return $item['diachi'];
        }, $diachi_cantang);

        self::$Arrdiachi_cantang = $diachi_cantang;
        self::$ArrThienCan = $thiencan;
        self::$ArrThienCanTong = $thiencantong;
        self::$DiemThienCan = $diem;

        self::$Menhchu = self::getMenh(self::$ArrThienCan[2]);

        $textHoaHop = [];

        // Gọi các hàm kiểm tra hóa hợp / tam hợp
        $giaky = self::checkGiapKy();
        $atcanh = self::checkAtCanh();
        $binhtan = self::checkBinhTan();
        $dinhnham = self::checkDinhNham();
        $canmau = self::checkCanMau();

        $tysuutho = self::checkTySuuTho();
        $ngomuitho = self::checkNgoMuiTho();
        $danhoimoc = self::checkDanHoiMoc();
        $maotuathoa = self::checkMaoTuatHoa();
        $thindaukim = self::checkThinDauKim();
        $tythanthuy = self::checkTyThanThuy();

        $danngotuathoa = self::checkDanNgoTuatHoa();

        if (!$danngotuathoa) {
            $danngohoa = self::checkDanNgoHoa();
            $ngotuathoa = self::checkNgoTuatHoa();
        }

        $hoimaomuimoc = self::checkHoiMaoMuiMoc();

        if (!$hoimaomuimoc) {
            $hoimaomoc = self::checkHoiMaoMoc();
            $maomuimoc = self::checkMaoMuiMoc();
        }

        $tydausuukim = self::checkTyDauSuuKim();

        if (!$tydausuukim) {
            $tydaukim = self::checkTyDauKim();
            $dausuukim = self::checkDauSuuKim();
        }

        $thantythinthuy = self::checkThanTyThinThuy();

        if (!$thantythinthuy) {
            $thantythuy = self::checkThanTyThuy();
            $tythinthuy = self::checkTyThinThuy();
        }

        $hoitysuuthuy = self::checkHoiTySuuThuy();
        $thandautuatkim = self::checkThanDauTuatKim();
        $danmaothinmoc = self::checkDanMaoThinMoc();
        $tyngomuihoa = self::checkTyNgoMuiHoa();
    }

    public static function tongdiemcantang($diachi_cantang, $thaydoidiachi)
    {
        $diemdiachi_cantang = self::tinhdiemCanTang($diachi_cantang);
        $arr_cantang = [];
        $arr_diemcantang = [];
        $arr_thaydoicantang = [];

        for ($i = 0; $i < 4; $i++) {
            if (!isset($diemdiachi_cantang[$i])) continue;

            $arr_cantang = array_merge($arr_cantang, $diemdiachi_cantang[$i]['cantang']);
            $arr_diemcantang = array_merge($arr_diemcantang, $diemdiachi_cantang[$i]['point']);
            $arr_thaydoicantang = array_merge($arr_thaydoicantang, $thaydoidiachi[$i]['point']);
        }

        // Cập nhật điểm Can Tàng sau thay đổi
        $arr_diemcantang = array_map(function ($item, $index) use ($arr_thaydoicantang) {
            $thaydoi = $arr_thaydoicantang[$index] ?? 0;
            $point = $item * ($thaydoi / 100) + $item;
            if ($point < 0) $point = 0;
            return round($point, 1);
        }, $arr_diemcantang, array_keys($arr_diemcantang));

        // Tính điểm chi tiết cho từng địa chi
        $diemcanchi = [];
        foreach ($diemdiachi_cantang as $index => $item) {
            $arrPoint = [];
            if (isset($item['point']) && is_array($item['point'])) {
                foreach ($item['point'] as $_index => $_item) {
                    $thaydoi = $thaydoidiachi[$index]['point'][$_index] ?? 0;
                    $point = $_item * ($thaydoi / 100) + $_item;
                    if ($point < 0) $point = 0;
                    $arrPoint[] = round($point, 1);
                }
            }
            $item['realPoint'] = $arrPoint;
            $diemcanchi[] = $item;
        }

        return [$arr_cantang, $arr_diemcantang, $diemcanchi];
    }

    public static function tinhdiemthiencan($thaydoithiencan)
    {
        $arrDiem = [];

        foreach ($thaydoithiencan as $item) {
            $thaydoi = $item;

            // Tính điểm theo công thức 36 + 36 * (thaydoi / 100)
            $point = round(36 + 36 * ($thaydoi / 100), 1);

            // Giới hạn điểm tối thiểu
            if ($point < 0) {
                $point = 0;
            }

            $arrDiem[] = $point;
        }

        return $arrDiem;
    }

    protected static $DiemcobanDiachi = [
        "Tý" => [
            "Quý" => 100
        ],
        "Sửu" => [
            "Kỷ" => 60,
            "Quý" => 30,
            "Tân" => 10
        ],
        "Dần" => [
            "Giáp" => 60,
            "Bính" => 30,
            "Mậu" => 10
        ],
        "Mão" => [
            "Ất" => 100
        ],
        "Thìn" => [
            "Mậu" => 60,
            "Ất" => 30,
            "Quý" => 10
        ],
        "Tỵ" => [
            "Bính" => 60,
            "Mậu" => 30,
            "Canh" => 10
        ],
        "Ngọ" => [
            "Đinh" => 70,
            "Kỷ" => 30
        ],
        "Mùi" => [
            "Kỷ" => 60,
            "Đinh" => 30,
            "Ất" => 10
        ],
        "Thân" => [
            "Canh" => 60,
            "Nhâm" => 30,
            "Mậu" => 10
        ],
        "Dậu" => [
            "Tân" => 100
        ],
        "Tuất" => [
            "Mậu" => 60,
            "Tân" => 30,
            "Đinh" => 10
        ],
        "Hợi" => [
            "Nhâm" => 70,
            "Giáp" => 30
        ],
    ];

    public static function tinhdiemCanTang($diachi_cantang)
    {

        $result = [];

        foreach ($diachi_cantang as $item) {
            $arrPoint = [];

            if (isset($item['cantang']) && is_array($item['cantang'])) {
                foreach ($item['cantang'] as $_item) {
                    $point = self::$DiemcobanDiachi[$item['diachi']][$_item] ?? 0;
                    $arrPoint[] = $point;
                }
            }

            $row = $item;
            $row['point'] = $arrPoint;

            $result[] = $row;
        }

        return $result;
    }

    public static function tongthaydoi($daivan, $thiencanInput, $diachi_cantang, $ngaysinh, $Songay)
    {
        $thaydoidaivan =  self::thaydoidaivan($daivan, $thiencanInput, $diachi_cantang);
        $thaydoiNguyenLenh = self::thaydoiNguyenLenh($thiencanInput, $diachi_cantang, $ngaysinh, $Songay);
        $thaydoiTrongTru = self::thaydoiTrongTru($thiencanInput, $diachi_cantang);
        $tongthaydoiThiencan = self::SumArray([
            $thaydoidaivan[0],
            $thaydoiNguyenLenh[0],
            $thaydoiTrongTru[0]
        ]);


        $tongthaydoicantang = self::sumArrCantang(
            $thaydoidaivan[1],
            $thaydoiNguyenLenh[1],
            $thaydoiTrongTru[1]
        );

        return [$tongthaydoiThiencan, $tongthaydoicantang];
    }



    protected static $ThiencanDaiVan = [
        'Giáp' => [100, 50, 30, 20, -50, -30, -20, 0, -20, -30],
        'Ất' => [50, 100, 20, 30, -30, -50, 0, -20, -30, -20],
        'Bính' => [-20, -30, 100, 50, 20, 30, -50, -30, -20, 0],
        'Đinh' => [-30, -20, 50, 100, 30, 20, -30, -50, 0, -20],
        'Mậu' => [0, 0, -20, -30, 100, 50, 20, 30, -50, -30],
        'Kỷ' => [0, 0, -30, -20, 50, 100, 30, 20, -30, -50],
        'Canh' => [-50, -30, 0, 0, -20, -30, 100, 50, 20, 30],
        'Tân' => [-30, -50, 0, 0, -30, -20, 50, 100, 30, 20],
        'Nhâm' => [20, 30, -50, -30, 0, 0, -20, -30, 100, 50],
        'Quý' => [30, 20, -30, -50, 0, 0, -30, -20, 50, 100],
    ];

    protected static $Diachidaivan = [
        "Tý" => [
            "Sửu" => [
                "Kỷ" => 0,
                "Quý" => 100,
                "Tân" => -20
            ],
            "Mão" => ["Ất" => 20],
            "Thìn" => [
                "Mậu" => -30,
                "Ất" => 20,
                "Quý" => 100
            ],
            "Ngọ" => [
                "Đinh" => -50,
                "Kỷ" => 0
            ],
            "Mùi" => [
                "Kỷ" => 0,
                "Đinh" => -50,
                "Ất" => 20
            ],
            "Thân" => [
                "Canh" => -30,
                "Nhâm" => 50,
                "Mậu" => -30
            ]
        ],
        "Sửu" => [
            "Tý" => ["Quý" => -30],
            "Tỵ" => [
                "Bính" => -40,
                "Mậu" => 40,
                "Canh" => 30
            ],
            "Ngọ" => [
                "Đinh" => -30,
                "Kỷ" => 70
            ],
            "Mùi" => [
                "Kỷ" => 100,
                "Đinh" => -30,
                "Ất" => -30
            ],
            "Dậu" => ["Tân" => 20],
            "Tuất" => [
                "Mậu" => 40,
                "Tân" => 30,
                "Đinh" => -70
            ]
        ],
        "Dần" => [
            "Tỵ" => [
                "Bính" => 50,
                "Mậu" => -30,
                "Canh" => -60
            ],
            "Ngọ" => [
                "Đinh" => 40,
                "Kỷ" => 20
            ],
            "Thân" => [
                "Canh" => -40,
                "Nhâm" => -40,
                "Mậu" => -30
            ],
            "Tuất" => [
                "Mậu" => -40,
                "Tân" => -30,
                "Đinh" => 80
            ],
            "Hợi" => [
                "Nhâm" => -40,
                "Giáp" => 80
            ]
        ],
        "Mão" => [
            "Tý" => ["Quý" => -20],
            "Thìn" => [
                "Mậu" => -30,
                "Ất" => 100,
                "Quý" => -20
            ],
            "Mùi" => [
                "Kỷ" => -50,
                "Đinh" => 20,
                "Ất" => 100
            ],
            "Dậu" => ["Tân" => -20],
            "Tuất" => [
                "Mậu" => -30,
                "Tân" => -20,
                "Đinh" => 20
            ]
        ],
        "Thìn" => [
            "Tý" => ["Quý" => -40],
            "Mão" => ["Ất" => 30],
            "Thìn" => [
                "Mậu" => 80,
                "Ất" => 100,
                "Quý" => -50
            ],
            "Thân" => [
                "Canh" => 20,
                "Nhâm" => -80,
                "Mậu" => 70
            ],
            "Dậu" => ["Tân" => 20],
            "Tuất" => [
                "Mậu" => 80,
                "Tân" => 20,
                "Đinh" => -10
            ]
        ],
        "Tỵ" => [
            "Sửu" => [
                "Kỷ" => 50,
                "Quý" => -30,
                "Tân" => -20
            ],
            "Dần" => [
                "Giáp" => -20,
                "Bính" => 80,
                "Mậu" => 100
            ],
            "Thân" => [
                "Canh" => -40,
                "Nhâm" => -70,
                "Mậu" => 100
            ],
            "Dậu" => ["Tân" => -20],
            "Hợi" => [
                "Nhâm" => -40,
                "Giáp" => -20
            ]
        ],
        "Ngọ" => [
            "Tý" => ["Quý" => -40],
            "Sửu" => [
                "Kỷ" => 70,
                "Quý" => -70,
                "Tân" => -30
            ],
            "Dần" => [
                "Giáp" => -30,
                "Bính" => 30,
                "Mậu" => 80
            ],
            "Ngọ" => [
                "Đinh" => 80,
                "Kỷ" => 100
            ],
            "Mùi" => [
                "Kỷ" => 70,
                "Đinh" => 80,
                "Ất" => -20
            ],
            "Tuất" => [
                "Mậu" => 50,
                "Tân" => -40,
                "Đinh" => 80
            ]
        ],
        "Mùi" => [
            "Tý" => ["Quý" => -70],
            "Sửu" => [
                "Kỷ" => 80,
                "Quý" => -70,
                "Tân" => -30
            ],
            "Mão" => ["Ất" => -20],
            "Ngọ" => [
                "Đinh" => 70,
                "Kỷ" => 80
            ],
            "Tuất" => [
                "Mậu" => 60,
                "Tân" => -20,
                "Đinh" => 80
            ],
            "Hợi" => [
                "Nhâm" => 40,
                "Giáp" => -30
            ]
        ],
        "Thân" => [
            "Tý" => ["Quý" => 50],
            "Dần" => [
                "Giáp" => -40,
                "Bính" => -50,
                "Mậu" => -20
            ],
            "Thìn" => [
                "Mậu" => -20,
                "Ất" => 0,
                "Quý" => 80
            ],
            "Tỵ" => [
                "Bính" => -30,
                "Mậu" => -20,
                "Canh" => 80
            ],
            "Hợi" => [
                "Nhâm" => 70,
                "Giáp" => -30
            ]
        ],
        "Dậu" => [
            "Sửu" => [
                "Kỷ" => -20,
                "Quý" => 20,
                "Tân" => 100
            ],
            "Mão" => ["Ất" => -50],
            "Thìn" => [
                "Mậu" => -30,
                "Ất" => -50,
                "Quý" => 20
            ],
            "Tỵ" => [
                "Bính" => -20,
                "Mậu" => -30,
                "Canh" => 50
            ],
            "Dậu" => ["Tân" => 100],
            "Tuất" => [
                "Mậu" => -30,
                "Tân" => 100,
                "Đinh" => 0
            ]
        ],
        "Tuất" => [
            "Sửu" => [
                "Kỷ" => 40,
                "Quý" => -20,
                "Tân" => 100
            ],
            "Dần" => [
                "Giáp" => -10,
                "Bính" => -30,
                "Mậu" => 70
            ],
            "Mão" => ["Ất" => -20],
            "Thìn" => [
                "Mậu" => 80,
                "Ất" => -50,
                "Quý" => -10
            ],
            "Ngọ" => [
                "Đinh" => -30,
                "Kỷ" => 30
            ],
            "Mùi" => [
                "Kỷ" => 40,
                "Đinh" => -30,
                "Ất" => -50
            ],
            "Dậu" => ["Tân" => 80]
        ],
        "Hợi" => [
            "Dần" => [
                "Giáp" => 80,
                "Bính" => -30,
                "Mậu" => -50
            ],
            "Mão" => ["Ất" => 50],
            "Tỵ" => [
                "Bính" => -40,
                "Mậu" => -50,
                "Canh" => -40
            ],
            "Mùi" => [
                "Kỷ" => -20,
                "Đinh" => 0,
                "Ất" => 80
            ],
            "Thân" => [
                "Canh" => -30,
                "Nhâm" => 100,
                "Mậu" => -50
            ],
            "Hợi" => [
                "Nhâm" => 90,
                "Giáp" => 100
            ]
        ]
    ];

    protected static $NguyetLenh = [
        [
            "month" => "Dần,Mão,Thìn",
            "day_month" => ["Thìn" => 12],
            "data" => [100, 100, 30, 30, -50, -50, -30, -30, -20, -20]
        ],
        [
            "month" => "Tỵ,Ngọ,Mùi",
            "day_month" => ["Mùi" => 12],
            "data" => [-20, -20, 100, 100, 30, 30, -50, -50, -30, -30]
        ],
        [
            "month" => "Thân,Dậu,Tuất",
            "day_month" => ["Tuất" => 12],
            "data" => [-50, -50, -30, -30, -20, -20, 100, 100, 30, 30]
        ],
        [
            "month" => "Hợi,Tý,Sửu",
            "day_month" => ["Sửu" => 12],
            "data" => [30, 30, -50, -50, -30, -30, -20, -20, 100, 100]
        ],
        [
            "month" => "Thìn,Mùi,Tuất,Sửu",
            "day_month" => ["Thìn" => 18],
            "data" => [-30, -30, -20, -20, 100, 100, 30, 30, -50, -50]
        ]
    ];

    protected static $TrongTru = [
        [
            'value' => 0,
            'canchi' => "Giáp Thìn",
            'cantang' => [['Mậu' => -70]],
        ],
        [
            'value' => 20,
            'canchi' => "Giáp Tý, Đinh Mão, Tân Mùi, Quý Dậu, Canh Thìn",
            'cantang' => [
                ['Quý' => -20],
                ['Ất' => -20],
                ['Kỷ' => -20],
                ['Tân' => -20],
                ['Mậu' => -20],
            ],
        ],
        [
            'value' => -30,
            'canchi' => "Ất Sửu,Giáp Tuất,Đinh Sửu, Nhâm Ngọ, Bính Tuất, Mậu Tý, Canh Dần, Tân Mão,Quý Tỵ, Giáp Ngọ, Ất Mùi, Bính Thân, Đinh Dậu, Kỷ Hợi, Canh Tý,Nhâm Dần, Quý Mão,Ất Tỵ, Đinh Mùi, Mậu Thân, Kỷ Dậu, Tân Hợi, Bính Thìn",
            'cantang' => [
                ['Kỷ' => -50],
                ['Mậu' => -50],
                ['Kỷ' => 30],
                ['Đinh' => -45],
                ['Mậu' => 30],
                ['Quý' => -45],
                ['Giáp' => -45],
                ['Ất' => -45],
                ['Bính' => -45],
                ['Đinh' => 30],
                ['Kỷ' => -50],
                ['Canh' => -45],
                ['Tân' => -45],
                ['Nhâm' => -45],
                ['Quý' => 30],
                ['Giáp' => 30],
                ['Ất' => 30],
                ['Bính' => 30],
                ['Kỷ' => 30],
                ['Canh' => 30],
                ['Tân' => 30],
                ['Nhâm' => 30],
                ['Mậu' => 30],
            ],
        ],
        [
            'value' => -40,
            'canchi' => "Nhâm Thìn,Quý Sửu",
            'cantang' => [['Mậu' => -30], ['Kỷ' => -30]],
        ],
        [
            'value' => -50,
            'canchi' => "Canh Ngọ, Bính Tý, Mậu Dần, Kỷ Mão, Tân Tỵ,Quý Mùi, Giáp Thân, Ất Dậu, Đinh Hợi, Nhâm Tuất",
            'cantang' => [
                ['Đinh' => -30],
                ['Quý' => -25],
                ['Giáp' => -30],
                ['Ất' => -25],
                ['Bính' => -30],
                ['Kỷ' => -25],
                ['Canh' => -30],
                ['Tân' => -25],
                ['Nhâm' => -30],
                ['Mậu' => -25],
            ],
        ],
        [
            'value' => 30,
            'canchi' => "Bính Dần,Kỷ Tỵ,Nhâm Thân,Ất Hợi,Tân Sửu,Canh Tuất,Mậu Ngọ",
            'cantang' => [
                ['Giáp' => -30],
                ['Bính' => -30],
                ['Canh' => -30],
                ['Nhâm' => -30],
                ['Kỷ' => -30],
                ['Mậu' => -30],
                ['Đinh' => -30],
            ],
        ],
        [
            'value' => 50,
            'canchi' => "Mậu Thìn,Kỷ Sửu,Mậu Tuất,Bính Ngọ,Nhâm Tý, Giáp Dần,Ất Mão, Đinh Tỵ, Kỷ Mùi, Canh Thân, Tân Dậu,Quý Hợi",
            'cantang' => [
                ['Mậu' => 50],
                ['Kỷ' => 50],
                ['Mậu' => 50],
                ['Đinh' => 50],
                ['Quý' => 50],
                ['Giáp' => 50],
                ['Ất' => 50],
                ['Bính' => 50],
                ['Kỷ' => 50],
                ['Canh' => 50],
                ['Tân' => 50],
                ['Nhâm' => 50],
            ],
        ],
    ];

    public static function SumArray($arr)
    {
        $arrSum = [0, 0, 0, 0];

        for ($i = 0; $i < 4; $i++) {
            foreach ($arr as $item) {
                foreach ($item as $index => $value) {
                    if ($index === $i) {
                        $arrSum[$i] += $value;
                    }
                }
            }
        }

        return $arrSum;
    }

    public static function sumTwoArray($arr1, $arr2, $arr3)
    {
        $arr = [];
        foreach ($arr1 as $index => $value) {
            $v1 = isset($arr1[$index]) ? $arr1[$index] : 0;
            $v2 = isset($arr2[$index]) ? $arr2[$index] : 0;
            $v3 = isset($arr3[$index]) ? $arr3[$index] : 0;
            $arr[] = $v1 + $v2 + $v3;
        }
        return $arr;
    }

    public static function sumArrCantang($arr1, $arr2, $arr3)
    {
        for ($i = 0; $i < 4; $i++) {
            $arr1[$i]['point'] = self::sumTwoArray(
                $arr1[$i]['point'],
                $arr2[$i]['point'],
                $arr3[$i]['point']
            );
        }
        return $arr1;
    }

    public static function thaydoiTrongTru($thiencan, $diachi_cantang)
    {
        // Kết hợp Can-Chi (ví dụ: "Giáp Tý", "Ất Sửu", ...)
        $thiencan_diachi = [];
        foreach ($thiencan as $index => $can) {
            $thiencan_diachi[] = $can . ' ' . $diachi_cantang[$index]['diachi'];
        }

        $arrThiencan = [];
        $result = [];

        // Clone dữ liệu gốc, thêm key 'point' khởi tạo = 0
        foreach ($diachi_cantang as $item) {
            $item['point'] = array_fill(0, count($item['cantang']), 0);
            $result[] = $item;
        }

        // Duyệt từng trụ (Can-Chi)
        foreach ($thiencan_diachi as $indexTru => $canchi) {
            foreach (self::$TrongTru as $rule) {
                if (strpos($rule['canchi'], $canchi) !== false) {
                    // ✅ Có cặp Can-Chi trong bảng Trọng Trụ
                    $arrThiencan[] = $rule['value'];

                    // Chuyển danh sách can-chi thành mảng
                    $arrCanchi = array_map('trim', explode(',', $rule['canchi']));

                    // Tìm vị trí khớp cặp Can-Chi
                    $matchIndex = array_search($canchi, $arrCanchi);
                    $cangtangObj = isset($rule['cantang'][$matchIndex]) ? $rule['cantang'][$matchIndex] : [];

                    // Lấy key-value từ object (vd: {"Kỷ" => -50})
                    $key = key($cangtangObj);
                    $val = current($cangtangObj);

                    // Áp dụng điểm cho can tàng trong trụ tương ứng
                    foreach ($result[$indexTru]['cantang'] as $i => $c) {
                        $result[$indexTru]['point'][$i] = ($c == $key) ? $val : 0;
                    }
                }
            }
        }

        return [$arrThiencan, $result];
    }

    public static function thaydoiNguyenLenh($tc, $dc, $ngaysinh, $Songay)
    {
        $tc_thangsinh = $tc[1];
        $dc_thangsinh = $dc[1]['diachi'];
        $data = array_fill(0, 10, 0);
        $flag = true;

        // Giả sử bạn có biến $Songay từ ngày sinh (lấy ngày trong tháng)
        $arrMonth = ['Dần', 'Mão', 'Thìn', 'Tỵ', 'Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi', 'Tý', 'Sửu'];

        foreach (self::$NguyetLenh as $item) {
            if (strpos($item['month'], $dc_thangsinh) !== false) {
                if (isset($item['day_month'][$dc_thangsinh]) && $flag) {
                    $dayLimit = $item['day_month'][$dc_thangsinh];
                    if (($dayLimit == 12 && $Songay <= 12) || ($dayLimit == 18 && $Songay > 12)) {
                        $data = $item['data'];
                        $flag = false;
                    }
                } elseif ($flag) {
                    $data = $item['data'];
                }
            }
        }
        // Mảng điểm thiên can
        $arrDiemThiencan = [];
        foreach ($tc as $item) {
            $index = array_search($item, self::$stems);
            $arrDiemThiencan[] = $index !== false ? $data[$index] : 0;
        }

        // Tính điểm địa chi
        $result = [];
        foreach ($dc as $item) {
            $arrPoint = [];
            foreach ($item['cantang'] as $_item) {
                $index = array_search($_item, self::$stems);
                $arrPoint[] = $index !== false ? $data[$index] : 0;
            }
            $item['point'] = $arrPoint;
            $result[] = $item;
        }
        return [$arrDiemThiencan, $result];
    }

    public static function thaydoidaivan($daivan, $thiencanInput, $diachi_cantang)
    {
        $parts = explode(' ', $daivan);

        $thiencan_dv = $parts[0] ?? '';
        $diachi_dv = $parts[1] ?? '';

        $arrThiencan_dv = [];

        $arrThiencan = self::$ThiencanDaiVan[$thiencan_dv] ?? [];

        foreach ($thiencanInput as $item) {
            $index = array_search($item, self::$stems);
            if ($index !== false && isset($arrThiencan[$index])) {
                $arrThiencan_dv[] = $arrThiencan[$index];
            } else {
                $arrThiencan_dv[] = null;
            }
        }

        $arrDiachi = self::$Diachidaivan[$diachi_dv] ?? [];

        $data_new = json_decode(json_encode($diachi_cantang), true);
        $result = [];
        foreach ($data_new as $item) {
            $arrPoint = [];

            if (isset($item['cantang']) && is_array($item['cantang'])) {
                foreach ($item['cantang'] as $_item) {
                    $point = $arrDiachi[$item['diachi']][$_item] ?? 0;
                    $arrPoint[] = $point;
                }
            }

            $row = $item;
            $row['point'] = $arrPoint;

            $result[] = $row;
        }
        return [$arrThiencan_dv, $result];
    }
}
